<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\budget;
use App\Models\cycle;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_budgetaire_sortie;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\element_ligne_budgetaire_entree;
use App\Models\entite;
use App\Models\Etudiant;
use App\Models\facture_etudiant;
use App\Models\filiere;
use App\Models\frais;
use App\Models\ligne_budgetaire_Entree;
use App\Models\niveau;
use App\Models\scolarite;
use App\Models\specialite;
use App\Models\tranche_scolarite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF; // barryvdh/laravel-dompdf
class FactureEtudiantController extends Controller
{
    /* ================== HELPERS ================== */

    /** Séquence par année académique (N°) */
    protected function generateNumeroFacture(int $anneeId): int
    {
        $last = facture_etudiant::where('id_annee_academique', $anneeId)->max('numero_facture');
        return (int) $last + 1;
    }

    /** Matricule : CODE_SPECIALITE + AA + compteur (0001) */
    protected function generateMatricule(Etudiant $etudiant, int $specialiteId, int $anneeId): ?string
    {
        if (!empty($etudiant->matricule)) return $etudiant->matricule;

        $spec  = specialite::find($specialiteId);
        $annee = annee_academique::find($anneeId);
        if (!$spec || !$annee) return null;

        // adapte le nom du champ code si besoin
        $code = $spec->code_specialite ?? ('SP'.$spec->id);

        // "2025-2026" → "25"
        if (preg_match('/\d{4}/', (string)$annee->nom, $m)) $aa = substr($m[0], -2);
        else $aa = date('y');

        $prefix = $code.$aa;
        $count  = Etudiant::where('matricule', 'LIKE', $prefix.'%')->count() + 1;

        return sprintf('%s%04d', $prefix, $count);
    }

    /** Première tranche trouvée (ou 0) */
    protected function pickTrancheIdFromScolarite(int $scolariteId): int
    {
        $first = tranche_scolarite::where('id_scolarite', $scolariteId)
            ->orderBy('date_limite')
            ->first();
        return $first?->id ?? 0;
    }

    /* ================== PAGES ================== */

    /** Liste des factures d’un étudiant + form */
    public function indexByEtudiant($etudiantId)
    {
        $etudiant = Etudiant::findOrFail($etudiantId);

        $factures = facture_etudiant::with([
            'cycles','filieres','niveaux','specialites',
            'scolarites','tranche_scolarites','frais',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree','Annee_academique','entite'
        ])
            ->where('id_etudiant', $etudiantId)
            ->orderBy('date_facture', 'desc')
            ->get();

        $title    = "Factures de l'étudiant : {$etudiant->nom}";
        $cycles   = cycle::orderBy('nom_cycle')->get();
        $filieres = filiere::orderBy('nom_filiere')->get();
        $fraisList= frais::orderBy('nom_frais')->get();
        $annees   = annee_academique::orderBy('created_at', 'desc')->get();
        $entites   = entite::orderBy('created_at', 'desc')->get();
        $budgets  = budget::orderBy('created_at', 'desc')->get();

        return view('Admin.FactureEtudiant.index', compact(
            'title','etudiant','factures','cycles','filieres','entites','fraisList','annees','budgets'
        ));
    }

    /* ================== AJAX PÉDAGO ================== */

    /** niveaux / specialites / scolarites depuis cycle+filiere */
    public function filtersFromCycleFiliere(Request $request)
    {
        $request->validate([
            'id_cycle'   => 'required|integer|min:1',
            'id_filiere' => 'required|integer|min:1',
        ]);

        $id_cycle   = (int) $request->id_cycle;
        $id_filiere = (int) $request->id_filiere;

        $sco = scolarite::with(['niveaux','specialites'])
            ->where('id_cycle', $id_cycle)
            ->where('id_filiere', $id_filiere)
            ->get();

        if ($sco->isEmpty()) {
            return response()->json(['niveaux'=>[], 'specialites'=>[], 'scolarites'=>[]]);
        }

        $niveauIds     = $sco->pluck('id_niveau')->filter()->unique()->values();
        $specialiteIds = $sco->pluck('id_specialite')->filter()->unique()->values();

        $niveaux = niveau::whereIn('id', $niveauIds)->orderBy('nom_niveau')->get(['id','nom_niveau']);
        $specialites = specialite::whereIn('id', $specialiteIds)->orderBy('nom_specialite')->get(['id','nom_specialite']);

        $scolarites = $sco->map(function($s){
            return [
                'id'    => $s->id,
                'label' => sprintf(
                    'Niv: %s | Spé: %s | Montant: %s',
                    $s->niveaux->nom_niveau ?? 'N/A',
                    $s->specialites->nom_specialite ?? 'N/A',
                    number_format($s->montant_total, 0, ',', ' ')
                ),
            ];
        })->values();

        return response()->json([
            'niveaux'     => $niveaux,
            'specialites' => $specialites,
            'scolarites'  => $scolarites,
        ]);
    }

    /** tranches par scolarité (affichage uniquement) */
    public function tranchesByScolarite($id)
    {
        $trs = tranche_scolarite::where('id_scolarite', (int)$id)
            ->orderBy('date_limite')
            ->get(['id','nom_tranche','montant_tranche','date_limite']);
        return response()->json($trs);
    }

    /* ================== AJAX BUDGET CASCADE ================== */

    /** Budget → Lignes (les lignes qui ont des données pour ce budget via données ligne) */
    public function ajaxLignesByBudget($budgetId)
    {
        $lignes = ligne_budgetaire_Entree::whereHas('donnee_ligne_budgetaire_entrees', function($q) use ($budgetId){
            $q->where('id_budget', (int)$budgetId);
        })
            ->orderBy('libelle_ligne_budgetaire_entree')
            ->get(['id','libelle_ligne_budgetaire_entree']);
        return response()->json($lignes);
    }

    /** Ligne → Éléments */
    public function ajaxElementsByLigne($ligneId)
    {
        $elts = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', (int)$ligneId)
            ->orderBy('libelle_elements_ligne_budgetaire_entree')
            ->get(['id','libelle_elements_ligne_budgetaire_entree']);
        return response()->json($elts);
    }

    /** Élément (+Budget) → Données BUDGETAIRES (parent) */
    public function ajaxDonneesBudgetairesByElement(Request $r, $elementId)
    {
        $r->validate(['id_budget' => 'required|integer|min:1']);
        $rows = donnee_budgetaire_entree::where('id_budget', (int)$r->id_budget)
            ->whereHas('donnee_ligne_budgetaire_entrees', function($q) use ($elementId){
                $q->where('id_element_ligne_budgetaire_entree', (int)$elementId);
            })
            ->orderBy('donnee_ligne_budgetaire_entree')
            ->get(['id','donnee_ligne_budgetaire_entree','montant']);
        return response()->json($rows);
    }

    /** Élément + Donnée BUDGETAIRE (+Budget) → Données LIGNE (enfant) */
    public function ajaxDonneesLigneByElementAndDonneeBudgetaire(Request $r, $elementId)
    {
        $r->validate([
            'id_budget'            => 'required|integer|min:1',
            'id_donnee_budgetaire' => 'required|integer|min:1',
        ]);
        $rows = donnee_ligne_budgetaire_entree::where('id_element_ligne_budgetaire_entree', (int)$elementId)
            ->where('id_budget', (int)$r->id_budget)
            ->where('id_donnee_budgetaire_entree', (int)$r->id_donnee_budgetaire)
            ->orderBy('donnee_ligne_budgetaire_entree')
            ->get(['id','donnee_ligne_budgetaire_entree','montant']);
        return response()->json($rows);
    }

    /* ================== CRUD ================== */

    /** Création */
    public function store(Request $r)
    {
        $r->validate([
            'id_etudiant'          => 'required|integer|exists:etudiants,id',
            'type_facture'         => 'required|in:0,1', // 0=frais, 1=scolarité
            'date_facture'         => 'required|date',
            'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',
            'id_entite'  => 'required|integer|exists:entites,id',

            'id_cycle'             => 'required|integer',
            'id_filiere'           => 'required|integer',
            'id_niveau'            => 'nullable|integer',
            'id_specialite'        => 'required|integer',
//            'id_scolarite'         => 'required|integer|exists:scolarites,id',
// CONDITIONNEL
            'id_scolarite' => 'required_if:type_facture,1',
            'id_frais'     => 'required_if:type_facture,0',
            // frais obligatoire si type=0
            'id_frais'             => 'required_if:type_facture,0|nullable|integer|exists:frais,id',

            // Budget cascade complète
            'id_budget'                         => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree'        => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'=> 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_budgetaire_entree'       => 'required|integer|exists:donnee_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree' => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',
        ]);
        if ($r->type_facture == 0) {
            $r->merge(['id_scolarite' => null]);
        }

        if ($r->type_facture == 1) {
            $r->merge(['id_frais' => null]);
        }

        return DB::transaction(function () use ($r) {
            $type     = (int) $r->type_facture;
            $anneeId  = (int) $r->id_annee_academique;
            $entiteId  = (int) $r->id_entite;
            $etudiant = Etudiant::findOrFail($r->id_etudiant);

            // Matricule auto si vide
            $newMatricule = $this->generateMatricule($etudiant, (int)$r->id_specialite, $anneeId);
            if ($newMatricule && empty($etudiant->matricule)) {
                $etudiant->update(['matricule' => $newMatricule]);
            }

            // Numéro auto
            $numero = $this->generateNumeroFacture($anneeId);

            // Montant + IDs frais/sco
            $idFrais = 0; $montant = 0.0;

            if ($type === 1) { // scolarité
                $sc = scolarite::findOrFail($r->id_scolarite);
                $montant = (float) $sc->montant_total;
                $idFrais = 0;
            } else { // frais
                $fr = frais::findOrFail((int) $r->id_frais);
                $idFrais = (int) $fr->id;
                $montant = (float) $fr->montant;
            }

            // Tranche auto
            $trancheId = $this->pickTrancheIdFromScolarite((int)$r->id_scolarite);

            $facture = facture_etudiant::create([
                'id_etudiant'   => (int)$r->id_etudiant,
                'id_cycle'      => (int)$r->id_cycle,
                'id_filiere'    => (int)$r->id_filiere,
                'id_niveau'     => (int)($r->id_niveau ?? 0),
                'id_specialite' => (int)$r->id_specialite,
                'type_facture'  => $type,
                'id_scolarite'  => (int)$r->id_scolarite,
                'id_tranche_scolarite' => $trancheId,
                'id_frais'      => $idFrais, // 0 si scolarité

                'id_budget'     => (int)$r->id_budget,
                'id_ligne_budgetaire_entree' => (int)$r->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
                'id_donnee_budgetaire_entree'        => (int)$r->id_donnee_budgetaire_entree,
                'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,

                'numero_facture'=> $numero,
                'date_facture'  => $r->date_facture,
                'id_annee_academique' => $anneeId,
                'id_entite' => $entiteId,
                'montant_total_facture' => $montant,
                'id_user'       => Auth::id() ?? 0,
            ]);

            // PDF (2 exemplaires sur A4 si dompdf dispo)
            if (class_exists(\PDF::class)) {
                $this->generateInvoicePdf($facture->id);
            }


            return back()->with('success', 'Facture enregistrée ✅ (N° '.$numero.')');
        });
    }

    /** Mise à jour (toujours tranche auto) */
    public function update(Request $r)
    {
        $r->validate([
            'id'                   => 'required|integer|exists:facture_etudiants,id',
            'type_facture'         => 'required|in:0,1',
            'date_facture'         => 'required|date',
            'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',
            'id_entite'  => 'required|integer|exists:entites,id',

            'id_cycle'             => 'required|integer',
            'id_filiere'           => 'required|integer',
            'id_niveau'            => 'nullable|integer',
            'id_specialite'        => 'required|integer',
            'id_scolarite'         => 'required|integer|exists:scolarites,id',

            'id_frais'             => 'required_if:type_facture,0|nullable|integer|exists:frais,id',

            'id_budget'                         => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree'        => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'=> 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_budgetaire_entree'       => 'required|integer|exists:donnee_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree' => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',
        ]);

        return DB::transaction(function () use ($r) {
            $f = facture_etudiant::findOrFail($r->id);
            $type = (int) $r->type_facture;

            $idFrais = 0; $montant = 0.0;
            if ($type === 1) {
                $sc = scolarite::findOrFail($r->id_scolarite);
                $montant = (float) $sc->montant_total;
                $idFrais = 0;
            } else {
                $fr = frais::findOrFail((int) $r->id_frais);
                $idFrais = (int) $fr->id;
                $montant = (float) $fr->montant;
            }

            // Tranche auto (ou 0)
            $trancheId = $this->pickTrancheIdFromScolarite((int)$r->id_scolarite);

            $f->update([
                'id_cycle'      => (int)$r->id_cycle,
                'id_filiere'    => (int)$r->id_filiere,
                'id_niveau'     => (int)($r->id_niveau ?? 0),
                'id_specialite' => (int)$r->id_specialite,
                'type_facture'  => $type,
                'id_scolarite'  => (int)$r->id_scolarite,
                'id_tranche_scolarite' => $trancheId,
                'id_frais'      => $idFrais,

                'date_facture'  => $r->date_facture,
                'id_annee_academique' => (int)$r->id_annee_academique,
                'id_entite' => (int)$r->id_entite,
                'montant_total_facture' => $montant,

                'id_budget'     => (int)$r->id_budget,
                'id_ligne_budgetaire_entree' => (int)$r->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
                'id_donnee_budgetaire_entree'        => (int)$r->id_donnee_budgetaire_entree,
                'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,
            ]);

            if (class_exists(\PDF::class)) {
                $this->generateInvoicePdf($f->id);
            }

            return back()->with('success', 'Facture modifiée ✏️ (N° '.$f->numero_facture.')');
        });
    }

    public function destroy($id)
    {
        facture_etudiant::findOrFail($id)->delete();
        return back()->with('success', 'Facture supprimée 🗑️');
    }

    /* ================== PDF ================== */

    /** Génération PDF 2 exemplaires A4 */
    protected function generateInvoicePdf1(int $factureId): void
    {
        if (!class_exists(\PDF::class)) return;

        $f = facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','frais',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree'
        ])->findOrFail($factureId);

        $tranches = tranche_scolarite::where('id_scolarite', $f->id_scolarite)
            ->orderBy('date_limite')->get();

        $pdf = \PDF::loadView('Admin.FactureEtudiant.pdf', [
            'facture' => $f,
            'etudiant'=> $f->etudiants,
            'tranches'=> $tranches,
        ])->setPaper('a4', 'portrait');
//        $title    = "Factures de l'étudiant : {$f->etudiants->nom}";
        $path = "factures/FACT-{$f->numero_facture}.pdf";
        Storage::disk('public')->put($path, $pdf->output());
    }
    protected function generateInvoicePdftest(int $factureId): void
    {
        if (!class_exists(\PDF::class)) return;

        $f = \App\Models\facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','frais','Annee_academique','entite','user',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree'
        ])->findOrFail($factureId);

        $tranches = \App\Models\tranche_scolarite::where('id_scolarite', $f->id_scolarite)
            ->orderBy('date_limite')->get();
// Nom du caissier = utilisateur rattaché à la facture (ou fallback sur l'actuel)
        $caissier = optional($f->user)->name ?? (optional(Auth::user())->name ?? '—');
// Vue Blade → 2 exemplaires sur A4 portrait
        $pdf = \PDF::loadView('Admin.FactureEtudiant.pdf', [
            'facture'  => $f,
            'etudiant' => $f->etudiants,
            'tranches' => $tranches,
            'caissier' => $caissier,
        ])->setPaper('a4', 'portrait');

        // 👉 Dossier souhaité : public/uploads/images/files/factures
        $dir  = 'uploads/images/files/factures';
        $file = "FACT-{$f->numero_facture}.pdf";
        $path = "$dir/$file";

        // Crée le dossier s'il n'existe pas
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($dir)) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($dir);
        }

        // Sauvegarde sur le disk 'public'
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdf->output());
    }
    protected function generateInvoicePdf(int $factureId): void
    {
        if (!class_exists(\PDF::class)) return;

        $f = \App\Models\facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','frais','Annee_academique','entite','user',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree'
        ])->findOrFail($factureId);

        $tranches = \App\Models\tranche_scolarite::where('id_scolarite', $f->id_scolarite)
            ->orderBy('date_limite')->get();

        $caissier = optional($f->user)->name ?? (optional(Auth::user())->name ?? '—');

        $pdf = \PDF::loadView('Admin.FactureEtudiant.pdf', [
            'facture'  => $f,
            'etudiant' => $f->etudiants,
            'tranches' => $tranches,
            'caissier' => $caissier,
        ])->setPaper('a4', 'portrait');

        // === Emplacement web PUBLIC (pas besoin de symlink) ===
        $publicDir  = public_path('uploads/images/files/factures');
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0775, true);
        }
        $publicFile = $publicDir . DIRECTORY_SEPARATOR . "FACT-{$f->numero_facture}.pdf";
        if (file_exists($publicFile)) {
            @unlink($publicFile);
        }
        file_put_contents($publicFile, $pdf->output());

        // (Optionnel) Sauvegarde aussi sur disk('public') si tu veux
        $diskPath = "uploads/images/files/factures/FACT-{$f->numero_facture}.pdf";
        if (!Storage::disk('public')->exists('uploads/images/files/factures')) {
            Storage::disk('public')->makeDirectory('uploads/images/files/factures');
        }
        Storage::disk('public')->put($diskPath, $pdf->output());
    }


    /** Affichage HTML imprimable (si pas de dompdf) */
    public function showPdf($id)
    {
        $facture = facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites.tranche_scolarite','tranche_scolarites','frais','budget',
            'ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','Annee_academique','entite','user','donnee_ligne_budgetaire_entree'
        ])->findOrFail($id);
        $title=" Gestion des factures étudiants";
        $tranches = $facture->scolarites?->tranche_scolarite ?? collect();
         $caissier = optional($facture->user)->name ?? (optional(Auth::user())->name ?? '—');
        return view('Admin.FactureEtudiant.pdf',
            compact('facture','title', 'tranches','caissier'))
            ->with('etudiant', $facture->etudiants);
    }
    public function downloadPdf($id)
    {
        // (re)génère avant de servir
        $this->generateInvoicePdf($id);
        $facture= \App\Models\facture_etudiant::findOrFail($id);
        $publicRel = "uploads/images/files/factures/FACT-{$facture->numero_facture}.pdf";
        $full = public_path($publicRel);

        if (!file_exists($full)) {
            return back()->with('error', "Impossible de générer le PDF.");
        }

        return response()
            ->download($full, "FACT-{$facture->numero_facture}.pdf")
            ->deleteFileAfterSend(false);
    }

    public function downloadPdftest($id)
    {
        if (!class_exists(\PDF::class)) {
            return redirect()->route('facture_pdf', $id);
        }

        // (re)génère avant le download pour être sûr
        $this->generateInvoicePdf($id);
        $facture = \App\Models\facture_etudiant::findOrFail($id);
        $path = "uploads/images/files/factures/FACT-{$facture->numero_facture}.pdf";

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', "Impossible de générer le PDF.");
        }

        $full = storage_path('app/public/'.$path);
        return response()->download($full, "FACT-{$facture->numero_facture}.pdf");
    }

    /** Téléchargement (si dompdf) */
    public function downloadPdf1($id)
    {
        if (!class_exists(\PDF::class)) {
            return redirect()->route('facture_pdf', $id);
        }

        $facture = facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites.tranche_scolarite','tranche_scolarites','frais','budget',
            'ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree'
        ])->findOrFail($id);

        $tranches = $facture->scolarites?->tranche_scolarite ?? collect();

        $pdf = \PDF::loadView('Admin.FactureEtudiant.pdf', [
            'facture' => $facture,
            'etudiant'=> $facture->etudiants,
            'tranches'=> $tranches,
        ])->setPaper('a4', 'portrait');

        $filename = 'Facture_'.$facture->numero_facture.'.pdf';
        return $pdf->download($filename);
    }
}
