<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\annee_academique;
use App\Models\banque;
use App\Models\budget;
use App\Models\caisse;
use App\Models\cycle;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_ligne_budgetaire_entree;
use App\Models\element_ligne_budgetaire_entree;
use App\Models\entite;
use App\Models\Etudiant;
use App\Models\facture_etudiant;
use App\Models\filiere;
use App\Models\ligne_budgetaire_Entree;
use App\Models\niveau;
use App\Models\reglement_etudiant;
use App\Models\scolarite;
use App\Models\specialite;
use App\Models\tranche_scolarite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReglementEtudiantController extends Controller
{
    /** Numéro séquentiel par année académique */
    protected function generateNumeroReglement1(int $anneeId): int
    {
        $last = reglement_etudiant::where('id_annee_academique', $anneeId)->max('numero_reglement');
        return (int)$last + 1;
    }

    /** Montant en lettres (fr) */
    protected function amountToWords1(float $amount): string
    {
        $fmt = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
        $euros = floor($amount);
        $cents = round(($amount - $euros) * 100);

        $txt = trim($fmt->format($euros)) . ' franc' . ($euros > 1 ? 's' : '');
        if ($cents > 0) {
            $txt .= ' et ' . trim($fmt->format($cents)) . ' centime' . ($cents > 1 ? 's' : '');
        }
        return ucfirst($txt);
    }

    /** Page: tous les règlements d’un étudiant + filtre période + bouton “Nouveau” depuis une facture */
    public function indexByEtudiant1(Request $r, $etudiantId)
    {
        $etudiant = Etudiant::findOrFail($etudiantId);
        $from = $r->get('from');
        $to   = $r->get('to');

        $query = reglement_etudiant::with(['cycles','filieres','niveaux','specialites',
            'scolarites','tranche_scolarites','frais','annee_academique','user',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree',
            'banque','caisse'
        ])->where('id_etudiant', $etudiantId);

        if ($from) $query->whereDate('date_reglement', '>=', $from);
        if ($to)   $query->whereDate('date_reglement', '<=', $to);

        $reglements = $query->orderBy('date_reglement', 'desc')->get();

        // Pour créer rapidement: listes de base
        $cycles    = cycle::orderBy('nom_cycle')->get();
        $filieres  = filiere::orderBy('nom_filiere')->get();
        $caisses   = caisse::orderBy('nom_caisse')
            ->get();
        $banques   = banque::orderBy('nom_banque')->get();
        $entites=entite::orderBy('nom_entite')->get();
        $annees    = annee_academique::orderBy('created_at', 'desc')->get();
        $budgets   = budget::orderBy('created_at', 'desc')->get();


        $title = "Règlements de l'étudiant : {$etudiant->nom}";
        return view('Admin.ReglementEtudiant.index', compact(
            'title','etudiant','reglements','cycles','filieres','caisses','banques','annees','budgets','entites','from','to'
        ));
    }

    /** Formulaire pré-rempli depuis une facture */
    public function createFromFacture1($factureId)
    {
        $facture = facture_etudiant::with(['etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','frais','annee_academique'])->findOrFail($factureId);

        // Totaux facture / déjà payé / reste
        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)->sum('montant_reglement');
        $reste = max(0, $totalFacture - $totalPaye);

        $cycles    = cycle::orderBy('nom_cycle')->get();
        $filieres  = filiere::orderBy('nom_filiere')->get();
        $caisses   = caisse::orderBy('nom_caisse')->where('type_caisse','=',0)->get();
        dump($caisses);
        die();
        $banques   = banque::orderBy('nom_banque')->get();
        $annees    = annee_academique::orderBy('created_at', 'desc')->get();
        $budgets   = budget::orderBy('created_at', 'desc')->get();

        return view('Admin.ReglementEtudiant.create_from_facture', compact(
            'facture','totalFacture','totalPaye','reste','cycles','filieres','caisses','banques','annees','budgets'
        ));
    }

    /** AJAX: filtres pédagogiques depuis cycle+filiere (comme facture) */
    public function filtersFromCycleFiliere1(Request $request)
    {
        $request->validate([
            'id_cycle'   => 'required|integer|min:1',
            'id_filiere' => 'required|integer|min:1',
        ]);

        $sco = scolarite::with(['niveaux','specialites'])
            ->where('id_cycle', (int)$request->id_cycle)
            ->where('id_filiere', (int)$request->id_filiere)
            ->get();

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

        return response()->json(compact('niveaux','specialites','scolarites'));
    }

    /** AJAX: tranches scolarité */
    public function tranchesByScolarite1($id)
    {
        $trs = tranche_scolarite::where('id_scolarite', (int)$id)
            ->orderBy('date_limite')
            ->get(['id','nom_tranche','montant_tranche','date_limite']);
        return response()->json($trs);
    }

    /** AJAX Budget */
    public function ajaxLignesByBudget1($budgetId)
    {
        $lignes = ligne_budgetaire_Entree::whereHas('donnee_ligne_budgetaire_entrees', function($q) use ($budgetId){
            $q->where('id_budget', (int)$budgetId);
        })->orderBy('libelle_ligne_budgetaire_entree')
            ->get(['id','libelle_ligne_budgetaire_entree']);
        return response()->json($lignes);
    }
    public function ajaxElementsByLigne1($ligneId)
    {
        $elts = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', (int)$ligneId)
            ->orderBy('libelle_elements_ligne_budgetaire_entree')
            ->get(['id','libelle_elements_ligne_budgetaire_entree']);
        return response()->json($elts);
    }
    public function ajaxDonneesByElement1(Request $r, $elementId)
    {
        $r->validate(['id_budget' => 'required|integer|min:1']);
        $donnees = donnee_ligne_budgetaire_entree::where('id_element_ligne_budgetaire_entree', (int)$elementId)
            ->where('id_budget', (int)$r->id_budget)
            ->orderBy('donnee_ligne_budgetaire_entree')
            ->get(['id','donnee_ligne_budgetaire_entree','montant']);
        return response()->json($donnees);
    }

    /** Création */
    public function store1(Request $r)
    {
        // NB: on n’utilise PAS d’accents dans les names du formulaire.
        $r->validate([
            'id_facture_etudiant'   => 'required|integer|exists:facture_etudiants,id',
            'id_etudiant'           => 'required|integer|exists:etudiants,id',
            'type_reglement'        => 'required|in:0,1', // 0=frais, 1=scolarité
            'date_reglement'        => 'required|date',
            'id_annee_academique'   => 'required|integer|exists:annee_academiques,id',

            'id_cycle'              => 'required|integer',
            'id_filiere'            => 'required|integer',
            'id_niveau'             => 'nullable|integer',
            'id_specialite'         => 'required|integer',
            'id_scolarite'          => 'required_if:type_reglement,1|integer|exists:scolarites,id',
            'id_tranche_scolarite'  => 'nullable|integer', // tranche OU all_tranches
            'all_tranches'          => 'nullable|boolean',

            'id_frais'              => 'required_if:type_reglement,0|nullable|integer|exists:frais,id',

            'id_budget'             => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree' => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'=> 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree' => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',

            'type_versement'        => 'required|in:espece,bancaire,orange,mtn',
            'id_caisse'             => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'             => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',

            'montant_reglement'     => 'required|numeric|min:0.01',
            'motif_reglement'       => 'nullable|string|max:255',
        ], [
            'id_scolarite.required_if' => 'La scolarité est requise pour un règlement de type scolarité.',
            'id_frais.required_if'     => 'Le frais est requis pour un règlement de type frais.',
            'id_caisse.required_if'    => 'Merci de sélectionner une caisse (espèces).',
            'id_banque.required_if'    => 'Merci de sélectionner une banque (bancaire).',
        ]);

        return DB::transaction(function () use ($r) {
            $facture = facture_etudiant::findOrFail($r->id_facture_etudiant);

            $anneeId = (int)$r->id_annee_academique;
            $numero  = $this->generateNumeroReglement($anneeId);
            $lettre  = $this->amountToWords((float)$r->montant_reglement);

            // mapping des colonnes accentuées en DB pour cohérence
            $data = [
                'id_cycle'   => (int)$r->id_cycle,
                // colonnes accentuées dans ta migration pour reglement :
                'id_filière'    => (int)$r->id_filiere,
                'id_scolarité'  => (int)($r->id_scolarite ?? 0),

                'id_niveau'     => (int)($r->id_niveau ?? 0),
                'id_specialite' => (int)$r->id_specialite,
                'id_frais'      => (int)($r->id_frais ?? 0),
                'id_tranche_scolarite' => (int)($r->id_tranche_scolarite ?? 0),

                'id_etudiant'   => (int)$r->id_etudiant,
                'id_budget'     => (int)$r->id_budget,
                'id_ligne_budgetaire_entree' => (int)$r->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
                'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,

                'montant_reglement' => (float)$r->montant_reglement,
                'numero_reglement'  => (int)$numero,
                'date_reglement'    => $r->date_reglement,
                'id_annee_academique' => $anneeId,
                'type_reglement'      => (int)$r->type_reglement,
                'id_user'             => Auth::id() ?? 0,
                'id_facture_etudiant' => (int)$r->id_facture_etudiant,
                'motif_reglement'     => $r->motif_reglement,

                'lettre'             => $lettre,
                'type_versement'     => $r->type_versement,
                'id_caisse'          => (int)($r->id_caisse ?? 0),
                'id_banque'          => (int)($r->id_banque ?? 0),
            ];

            $reg = new reglement_etudiant();
            $reg->fill($data);
            $reg->save();

            $this->generateReceiptPdf($reg->id);

            return back()->with('success', 'Règlement enregistré ✅ (N° '.$numero.')');
        });
    }

    /** Mise à jour (mêmes règles) */
    public function update1(Request $r)
    {
        $r->validate([
            'id'                    => 'required|integer|exists:reglement_etudiants,id',
            'type_reglement'        => 'required|in:0,1',
            'date_reglement'        => 'required|date',
            'id_annee_academique'   => 'required|integer|exists:annee_academiques,id',
            'id_cycle'              => 'required|integer',
            'id_filiere'            => 'required|integer',
            'id_niveau'             => 'nullable|integer',
            'id_specialite'         => 'required|integer',
            'id_scolarite'          => 'required_if:type_reglement,1|nullable|integer|exists:scolarites,id',
            'id_tranche_scolarite'  => 'nullable|integer',
            'id_frais'              => 'required_if:type_reglement,0|nullable|integer|exists:frais,id',
            'id_budget'             => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree' => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'=> 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree' => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',
            'type_versement'        => 'required|in:espece,bancaire,orange,mtn',
            'id_caisse'             => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'             => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',
            'montant_reglement'     => 'required|numeric|min:0.01',
            'motif_reglement'       => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($r) {
            $reg = reglement_etudiant::findOrFail($r->id);

            $lettre = $this->amountToWords((float)$r->montant_reglement);

            $reg->fill([
                'id_cycle'   => (int)$r->id_cycle,
                'id_filière' => (int)$r->id_filiere,
                'id_scolarité' => (int)($r->id_scolarite ?? 0),
                'id_niveau'     => (int)($r->id_niveau ?? 0),
                'id_specialite' => (int)$r->id_specialite,
                'id_frais'      => (int)($r->id_frais ?? 0),
                'id_tranche_scolarite' => (int)($r->id_tranche_scolarite ?? 0),

                'id_budget'     => (int)$r->id_budget,
                'id_ligne_budgetaire_entree' => (int)$r->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
                'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,

                'date_reglement'        => $r->date_reglement,
                'id_annee_academique'   => (int)$r->id_annee_academique,
                'type_reglement'        => (int)$r->type_reglement,
                'montant_reglement'     => (float)$r->montant_reglement,
                'motif_reglement'       => $r->motif_reglement,
                'lettre'                => $lettre,
                'type_versement'        => $r->type_versement,
                'id_caisse'             => (int)($r->id_caisse ?? 0),
                'id_banque'             => (int)($r->id_banque ?? 0),
            ]);
            $reg->save();

            $this->generateReceiptPdf($r->id);

            return back()->with('success', 'Règlement modifié ✏️ (N° '.$reg->numero_reglement.')');
        });
    }

    public function destroy1($id)
    {
        reglement_etudiant::findOrFail($id)->delete();
        return back()->with('success', 'Règlement supprimé 🗑️');
    }

    /** PDF reçu (2 souches A4 portrait + filigrane) */
    protected function generateReceiptPdf1(int $reglementId): void
    {
        if (!class_exists(\PDF::class)) return;

        $r = reglement_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','tranche_scolarites','frais','annee_academique','user',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree',
            'banque','caisse'
        ])->findOrFail($reglementId);

        $facture = facture_etudiant::with('scolarites','frais','etudiants')->findOrFail($r->id_facture_etudiant);

        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)->sum('montant_reglement');
        $reste = max(0, $totalFacture - $totalPaye);

        $tranches = collect();
        if ((int)$r->type_reglement === 1 && $r->id_scolarité) {
            $tranches = tranche_scolarite::where('id_scolarite', $r->id_scolarité)
                ->orderBy('date_limite')->get();
        }

        $caissier = optional($r->user)->name ?? (optional(Auth::user())->name ?? '—');

        $pdf = \PDF::loadView('Admin.ReglementEtudiant.pdf', [
            'reglement' => $r,
            'facture'   => $facture,
            'etudiant'  => $r->etudiants,
            'tranches'  => $tranches,
            'totalFacture' => $totalFacture,
            'totalPaye'    => $totalPaye,
            'reste'        => $reste,
            'caissier'     => $caissier,
        ])->setPaper('a4', 'portrait');

        $dir  = 'uploads/images/files/reglements';
        $file = "REG-{$r->numero_reglement}.pdf";
        $path = "$dir/$file";

        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($path, $pdf->output());
    }

    public function showPdf1($id)
    {
        $r = reglement_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','tranche_scolarites','frais','annee_academique','user',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree',
            'banque','caisse'
        ])->findOrFail($id);

        $facture = facture_etudiant::with('scolarites','frais','etudiants')->findOrFail($r->id_facture_etudiant);
        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)->sum('montant_reglement');
        $reste = max(0, $totalFacture - $totalPaye);

        $tranches = collect();
        if ((int)$r->type_reglement === 1 && $r->id_scolarité) {
            $tranches = tranche_scolarite::where('id_scolarite', $r->id_scolarité)->orderBy('date_limite')->get();
        }
        $caissier = optional($r->user)->name ?? (optional(Auth::user())->name ?? '—');

        return view('Admin.ReglementEtudiant.pdf', compact(
            'r','facture','tranches','totalFacture','totalPaye','reste','caissier'
        ))->with('reglement', $r)
            ->with('etudiant', $r->etudiants);
    }

    public function downloadPdf1($id)
    {
        if (!class_exists(\PDF::class)) {
            return redirect()->route('reglement_pdf', $id);
        }
        $this->generateReceiptPdf($id);

        $reg = reglement_etudiant::findOrFail($id);
        $path = "uploads/images/files/reglements/REG-{$reg->numero_reglement}.pdf";

        if (!Storage::disk('public')->exists($path)) {
            return back()->with('error', "Impossible de générer le PDF.");
        }

        $full = storage_path('app/public/'.$path);
        return response()->download($full, 'Reglement_'.$reg->numero_reglement.'.pdf');
    }



    /** INDEX : liste des règlements d’une facture + résumé */
    public function indexByFacture($factureId)
    {
        $facture = facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites','frais','budget','Annee_academique'
        ])->findOrFail($factureId);

        $etudiant = $facture->etudiants;

        $reglements = reglement_etudiant::with(['caisse','banque','user'])
            ->where('id_facture_etudiant', $factureId)
            ->orderBy('date_reglement', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye    = (float) reglement_etudiant::where('id_facture_etudiant', $factureId)->sum('montant_reglement');
        $reste        = max(0, $totalFacture - $totalPaye);

        return view('Admin.ReglementEtudiant.index', compact(
            'facture', 'etudiant', 'reglements', 'totalFacture', 'totalPaye', 'reste'
        ));
    }

    /** Créer un règlement depuis une facture (formulaire) */
    public function createFromFacture($factureId)
    {
        $facture = facture_etudiant::with([
            'etudiants','cycles','filieres','niveaux','specialites',
            'scolarites.tranche_scolarite','frais','Annee_academique','budget'
        ])->findOrFail($factureId);

        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye    = (float) reglement_etudiant::where('id_facture_etudiant', $factureId)->sum('montant_reglement');
        $reste        = max(0, $totalFacture - $totalPaye);

        $cycles   = cycle::orderBy('nom_cycle')->get();
        $filieres = filiere::orderBy('nom_filiere')->get();
        $caisses  = caisse::orderBy('nom_caisse')->where('type_caisse','=',0)->get();
        $banques  = banque::orderBy('nom_banque')->get();
        $annees   = annee_academique::orderBy('created_at', 'desc')->get();
        $budgets  = budget::orderBy('created_at', 'desc')->get();
        $entites=entite::orderBy('nom_entite')->get();

        return view('Admin.ReglementEtudiant.create_from_facture', compact(
            'facture','totalFacture','totalPaye','reste',
            'cycles','filieres','caisses','banques','annees','entites','budgets'
        ));
    }

    /** Enregistrer un règlement */

    public function store(Request $r)
    {
        // on ne demande plus ces champs au form, on les déduits :
        $r->validate([
            'id_facture_etudiant'   => 'required|integer|exists:facture_etudiants,id',
            'id_etudiant'           => 'required|integer|exists:etudiants,id',
            'id_annee_academique'   => 'required|integer|exists:annee_academiques,id',

            // versement seulement
            'type_versement'        => 'required|in:espece,bancaire,om,mtn',
            'id_caisse'             => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'             => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',

            // tranche éventuellement (si scolarité)
            'id_tranche_scolarite'  => 'nullable|integer|exists:tranche_scolarites,id',

            // montant / motif
            'montant_reglement'     => 'required|numeric|min:0.01',
            'motif_reglement'       => 'nullable|string|max:255',
            'date_reglement'        => 'required|date',
        ], [
            'id_caisse.required_if' => 'La caisse est obligatoire pour un règlement en espèces.',
            'id_banque.required_if' => 'La banque est obligatoire pour un règlement bancaire.',
        ]);


        // 1. on récupère la facture AVEC toutes les infos nécessaires
        $facture = facture_etudiant::with([
            'etudiants',
            'cycles',
            'filieres',
            'niveaux',
            'specialites',
            'scolarites',
            'budget',
            'entite',
        ])->findOrFail($r->id_facture_etudiant);

        // 2. calcul reste
        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye    = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)->sum('montant_reglement');
        $reste        = max(0, $totalFacture - $totalPaye);
        if ($r->montant_reglement > $reste) {
            return back()->withInput()->withErrors([
                'montant_reglement' => "Le montant dépasse le reste à payer (".number_format($reste,0,',',' ').")."
            ]);
        }

        // 3. toutes les infos pédagogiques et budgétaires viennent de la facture
        $idCycle       = (int)($facture->id_cycle ?? 0);
        $idFiliere     = (int)($facture->id_filiere ?? 0);
        $idNiveau      = (int)($facture->id_niveau ?? 0);
        $idSpecialite  = (int)($facture->id_specialite ?? 0);
        $idScolarite   = (int)($facture->id_scolarite ?? 0);
        $idEntite   = (int)($facture->id_entite ?? 0);
        $idAnnee   = (int)($facture->id_annee_academique ?? 0);

        // budget (si tu les mets sur la facture)
        $idBudget                  = (int)($facture->id_budget ?? 0);
        $idLigneBudget             = (int)($facture->id_ligne_budgetaire_entree ?? 0);
        $idElementLigneBudget      = (int)($facture->id_element_ligne_budgetaire_entree ?? 0);
        $idDonneeBudget            = (int)($facture->id_donnee_budgetaire_entree ?? 0);
        $idDonneeLigneBudget       = (int)($facture->id_donnee_ligne_budgetaire_entree ?? 0);

        // 4. type de règlement = celui de la facture
        $typeReglement = (int)$facture->type_facture; // 0 = frais, 1 = scolarité
        $idFrais       = $typeReglement === 0 ? (int)($facture->id_frais ?? 0) : 0;
        $idTranche     = $typeReglement === 1 ? (int)($r->id_tranche_scolarite ?? 0) : 0;

        return DB::transaction(function () use ($r, $facture, $typeReglement, $idFrais, $idTranche,
            $idCycle,$idFiliere,$idNiveau,$idSpecialite,$idScolarite,$idEntite,$idAnnee,
            $idBudget,$idLigneBudget,$idElementLigneBudget,$idDonneeBudget,$idDonneeLigneBudget) {

            // numéro
            $numero = $this->generateNumeroReglement((int)$r->id_annee_academique);

            // map type_versement -> INT si ta colonne est INT
            $map = ['espece'=>0,'bancaire'=>1,'om'=>2,'mtn'=>3];
            $typeVersementInt = $map[$r->type_versement] ?? 0;

            $data = [
                'id_facture_etudiant'                 => (int)$r->id_facture_etudiant,
                'id_etudiant'                         => (int)$r->id_etudiant,
               // 'id_annee_academique'                 => (int)$r->id_annee_academique,
                'date_reglement'                      => $r->date_reglement,
                'numero_reglement'                    => $numero,

                // pédagogie (toutes grisées dans le form)
                'id_cycle'                            => $idCycle,
                'id_filiere'                          => $idFiliere,
                'id_niveau'                           => $idNiveau,
                'id_specialite'                       => $idSpecialite,
                'id_scolarite'                        => $idScolarite,
                'id_entite'                        => $idEntite,
                'id_annee_academique'                 => $idAnnee,

                // type
                'type_reglement'                      => $typeReglement,
                'id_frais'                            => $idFrais,
                'id_tranche_scolarite'                => $idTranche,

                // versement
                'type_versement'                      => $typeVersementInt,
                'id_caisse'                           => (int)($r->id_caisse ?? 0),
                'id_banque'                           => (int)($r->id_banque ?? 0),

                // budget (pris sur la facture)
                'id_budget'                           => $idBudget,
                'id_ligne_budgetaire_entree'          => $idLigneBudget,
                'id_element_ligne_budgetaire_entree'  => $idElementLigneBudget,
                'id_donnee_budgetaire_entree'         => $idDonneeBudget,
                'id_donnee_ligne_budgetaire_entree'   => $idDonneeLigneBudget,

                // montant / motif
                'montant_reglement'                   => (float)$r->montant_reglement,
                'motif_reglement'                     => $r->motif_reglement,
                'lettre'                              => $this->toWordsFr((float)$r->montant_reglement).' francs CFA',

                // traçabilité
                'id_user'                             => Auth::id() ?? 0,
            ];

            $reglement = reglement_etudiant::create($data);

            // si tu veux générer le PDF tout de suite
            if (method_exists($this, 'generateReceiptPdf')) {
                $this->generateReceiptPdf($reglement->id);
            }

            return redirect()
                ->route('reglement_by_facture', $facture->id)
                ->with('success', 'Règlement N° '.$numero.' enregistré ✅');
        });
    }

    public function storeVO(Request $r)
    {
        // 0=frais, 1=scolarité (calqué sur la facture)
        $r->validate([
            'id_facture_etudiant'                 => 'required|integer|exists:facture_etudiants,id',
            'id_etudiant'                         => 'required|integer|exists:etudiants,id',
            'id_annee_academique'                 => 'required|integer|exists:annee_academiques,id',

            // --- PÉDAGOGIE ---
            'id_cycle'                            => 'required|integer|min:1',
            'id_filiere'                          => 'required|integer|min:1',
            'id_niveau'                           => 'nullable|integer|min:0',
            'id_specialite'                       => 'required|integer|min:1',
            'id_scolarite'                        => 'required|integer|exists:scolarites,id',

            // --- TYPE + TRANCHE / FRAIS ---
            'id_tranche_scolarite'                => 'nullable|integer|min:0', // 0=toutes, null=aucune
            'id_frais'                            => 'nullable|integer|min:0',

            // --- VERSEMENT ---
            'type_versement'                      => 'required|in:espece,bancaire,om,mtn',
            'id_caisse'                           => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'                           => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',

            // --- BUDGET ---
            'id_budget'                           => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree'          => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'  => 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_budgetaire_entree'         => 'required|integer|exists:donnee_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree'   => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',

            // --- MONTANT / MOTIF ---
            'montant_reglement'                   => 'required|numeric|min:0.01',
            'motif_reglement'                     => 'required',
            'lettre'                     => 'required',
            'date_reglement'                     => 'nullable|string|max:255',
        ], [
            'id_caisse.required_if' => 'La caisse est obligatoire pour un règlement en espèces.',
            'id_banque.required_if' => 'La banque est obligatoire pour un règlement bancaire.',
        ]);

        $facture = facture_etudiant::findOrFail($r->id_facture_etudiant);

        // Restes
        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye    = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)->sum('montant_reglement');
        $reste        = max(0, $totalFacture - $totalPaye);

        if ($r->montant_reglement > $reste) {
            return back()->withInput()->withErrors(['montant_reglement' =>
                "Le montant dépasse le reste à payer (".number_format($reste,0,',',' ').")."]);
        }

        return DB::transaction(function () use ($r, $facture) {

            // Numéro séquentiel par année
            $numero = $this->generateNumeroReglement((int)$r->id_annee_academique);

            // Map type_versement (si colonne INT)
            $map = ['espece'=>0,'bancaire'=>1,'om'=>2,'mtn'=>3];
            $typeVersementInt = $map[$r->type_versement] ?? 0;

            // Gère frais/scolarité
            $typeReglement = (int)$facture->type_facture; // 0=frais,1=scolarité
            $idFrais       = $typeReglement === 0 ? (int)($facture->id_frais ?? 0) : 0;
            $idTranche     = $typeReglement === 1 ? (int)($r->id_tranche_scolarite ?? 0) : 0;

            $data = [
                // Liens de base
                'id_facture_etudiant'                => (int)$r->id_facture_etudiant,
                'id_etudiant'                        => (int)$r->id_etudiant,
                'id_annee_academique'                => (int)$r->id_annee_academique,
                'date_reglement'                     => $r->date_reglement,
                'numero_reglement'                   => $numero,

                // Pédagogie (tous)
                'id_cycle'                           => (int)$r->id_cycle,
                'id_filiere'                         => (int)$r->id_filiere,
                'id_niveau'                          => (int)($r->id_niveau ?? 0),
                'id_specialite'                      => (int)$r->id_specialite,
                'id_scolarite'                       => (int)$r->id_scolarite,

                // Types
                'type_reglement'                     => $typeReglement,
                'id_tranche_scolarite'               => $idTranche,
                'id_frais'                           => $idFrais,

                // Versement
                'type_versement'                     => $typeVersementInt,         // ⚠️ INT en base
                // si string en base -> 'type_versement' => $r->type_versement,
                'id_caisse'                          => (int)($r->id_caisse ?? 0),
                'id_banque'                          => (int)($r->id_banque ?? 0),

                // Budget
                'id_budget'                          => (int)$r->id_budget,
                'id_ligne_budgetaire_entree'         => (int)$r->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
                'id_donnee_budgetaire_entree'        => (int)$r->id_donnee_budgetaire_entree,
                'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,

                // Montant / libellés
                'montant_reglement'                  => (float)$r->montant_reglement,
                'motif_reglement'                    => $r->motif_reglement,
                'lettre'                    => (string)$r->lettre,
           //   'lettre'                             => $this->toWordsFr((float)$r->montant_reglement).' francs CFA',

                // Traçabilité
                'id_user'                            => Auth::id() ?? 0,
            ];

            reglement_etudiant::create($data);

            return redirect()
                ->route('reglement_by_facture', $facture->id)
                ->with('success', 'Règlement N° '.$numero.' enregistré ✅');
        });
    }
    public function editvo($id)
    {
        $reglement = reglement_etudiant::with([
            'etudiants','caisse','banque','budget','ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree','donnee_budgetaire_entree','donnee_ligne_budgetaire_entree'
        ])->findOrFail($id);

        // données nécessaires aux selects
        $caisses = caisse::orderBy('nom_caisse')->get();
        $banques = banque::orderBy('nom_banque')->get();
        $budgets = budget::orderBy('created_at','desc')->get();
        $cycles   = cycle::orderBy('nom_cycle')->get();
        $filieres = filiere::orderBy('nom_filiere')->get();

        // renvoie ta vue d’édition (ex: Admin.ReglementEtudiant.edit)
        return view('Admin.ReglementEtudiant.edit', compact('reglement','caisses','banques','budgets','cycles','filieres'));
    }


    public function edit($id)
    {
        $reglement = reglement_etudiant::with([
            'etudiants',
            'caisse',
            'banque',
            'budget',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree',
            'donnee_ligne_budgetaire_entree',
            'facture_etudiants' // si tu as la relation
        ])->findOrFail($id);

        // on récupère la facture pour afficher ses données verrouillées
        $facture = facture_etudiant::with([
            'etudiants',
            'cycles',
            'filieres',
            'niveaux',
            'specialites',
            'scolarites.tranche_scolarite',
            'frais',
            'budget',
            'ligne_budgetaire_entree',
            'element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree',
            'donnee_ligne_budgetaire_entree'
        ])->findOrFail($reglement->id_facture_etudiant);

        // listes pour les champs encore éditables
        $caisses = caisse::orderBy('nom_caisse')->get();
        $banques = banque::orderBy('nom_banque')->get();

        return view('Admin.ReglementEtudiant.edit', compact(
            'reglement',
            'facture',
            'caisses',
            'banques'
        ));
    }
    public function update(Request $r)
    {
        $r->validate([
            'id'                   => 'required|integer|exists:reglement_etudiants,id',
            'id_facture_etudiant'  => 'required|integer|exists:facture_etudiants,id',
            'id_etudiant'          => 'required|integer|exists:etudiants,id',
            'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',

            // ce que l’utilisateur peut vraiment changer
            'type_versement'       => 'required|in:espece,bancaire,om,mtn',
            'id_caisse'            => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'            => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',
            'id_tranche_scolarite' => 'nullable|integer|exists:tranche_scolarites,id',
            'montant_reglement'    => 'required|numeric|min:0.01',
            'motif_reglement'      => 'nullable|string|max:255',
            'date_reglement'       => 'required|date',
        ], [
            'id_caisse.required_if' => 'La caisse est obligatoire pour un règlement en espèces.',
            'id_banque.required_if' => 'La banque est obligatoire pour un règlement bancaire.',
        ]);

        $reg = reglement_etudiant::findOrFail($r->id);
        $facture = facture_etudiant::with([
            'cycles','filieres','niveaux','specialites','scolarites','entite',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree'
        ])->findOrFail($r->id_facture_etudiant);

        // recalcul du reste dispo (on exclut ce règlement)
        $totalFacture = (float)$facture->montant_total_facture;
        $totalAutres  = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)
            ->where('id', '<>', $reg->id)
            ->sum('montant_reglement');
        $reste = max(0, $totalFacture - $totalAutres);
        if ($r->montant_reglement > $reste) {
            return back()->withInput()->withErrors([
                'montant_reglement' => "Le montant dépasse le reste disponible (".number_format($reste,0,',',' ').")."
            ]);
        }

        // on reprend tout depuis la facture
        $idCycle      = (int)($facture->id_cycle ?? 0);
        $idFiliere    = (int)($facture->id_filiere ?? 0);
        $idNiveau     = (int)($facture->id_niveau ?? 0);
        $idSpecialite = (int)($facture->id_specialite ?? 0);
        $idScolarite  = (int)($facture->id_scolarite ?? 0);
        $idEntite  = (int)($facture->id_entite ?? 0);
        $idAnnee  = (int)($facture->id_annee_academique ?? 0);

        $idBudget     = (int)($facture->id_budget ?? 0);
        $idLigne      = (int)($facture->id_ligne_budgetaire_entree ?? 0);
        $idElt        = (int)($facture->id_element_ligne_budgetaire_entree ?? 0);
        $idDonBud     = (int)($facture->id_donnee_budgetaire_entree ?? 0);
        $idDonLigne   = (int)($facture->id_donnee_ligne_budgetaire_entree ?? 0);

        // type de règlement vient de la facture
        $typeReglement = (int)$facture->type_facture; // 0=frais, 1=scolarité
        $idFrais       = $typeReglement === 0 ? (int)($facture->id_frais ?? 0) : 0;
        $idTranche     = $typeReglement === 1 ? (int)($r->id_tranche_scolarite ?? 0) : 0;

        // map pour la colonne INT
        $map = ['espece'=>0, 'bancaire'=>1, 'om'=>2, 'mtn'=>3];
        $typeVersementInt = $map[$r->type_versement] ?? 0;

        $reg->update([
            'id_facture_etudiant'                 => (int)$r->id_facture_etudiant,
            'id_etudiant'                         => (int)$r->id_etudiant,
            //'id_annee_academique'                 => (int)$r->id_annee_academique,
            'date_reglement'                      => $r->date_reglement,

            // pédagogie (verrouillé côté vue, mais on enregistre ce que dit la facture)
            'id_cycle'                            => $idCycle,
            'id_filiere'                          => $idFiliere,
            'id_niveau'                           => $idNiveau,
            'id_specialite'                       => $idSpecialite,
            'id_scolarite'                        => $idScolarite,
            'id_entite'                        => $idEntite,
            'id_annee_academique'                        => $idAnnee,

            // type
            'type_reglement'                      => $typeReglement,
            'id_frais'                            => $idFrais,
            'id_tranche_scolarite'                => $idTranche,

            // versement
            'type_versement'                      => $typeVersementInt,
            'id_caisse'                           => (int)($r->id_caisse ?? 0),
            'id_banque'                           => (int)($r->id_banque ?? 0),

            // budget (toujours depuis facture)
            'id_budget'                           => $idBudget,
            'id_ligne_budgetaire_entree'          => $idLigne,
            'id_element_ligne_budgetaire_entree'  => $idElt,
            'id_donnee_budgetaire_entree'         => $idDonBud,
            'id_donnee_ligne_budgetaire_entree'   => $idDonLigne,

            // montant
            'montant_reglement'                   => (float)$r->montant_reglement,
            'motif_reglement'                     => $r->motif_reglement,
            'lettre'                              => $this->toWordsFr((float)$r->montant_reglement).' francs CFA',
        ]);

        return redirect()
            ->route('reglement_by_facture', $facture->id)
            ->with('success', 'Règlement N° '.$reg->numero_reglement.' mis à jour ✅');
    }

    public function filtersFromCycleFiliere(Request $request)
    {
        $request->validate([
            'id_cycle'   => 'required|integer|min:1',
            'id_filiere' => 'required|integer|min:1',
        ]);

        $id_cycle   = (int) $request->id_cycle;
        $id_filiere = (int) $request->id_filiere;

        // On récupère les scolarités liant cycle+filiere
        $sco = \App\Models\scolarite::with(['niveaux','specialites'])
            ->where('id_cycle', $id_cycle)
            ->where('id_filiere', $id_filiere)
            ->get();

        if ($sco->isEmpty()) {
            return response()->json([
                'niveaux'     => [],
                'specialites' => [],
                'scolarites'  => [],
            ]);
        }

        $niveauIds     = $sco->pluck('id_niveau')->filter()->unique()->values();
        $specialiteIds = $sco->pluck('id_specialite')->filter()->unique()->values();

        $niveaux = \App\Models\niveau::whereIn('id', $niveauIds)
            ->orderBy('nom_niveau')->get(['id','nom_niveau']);

        $specialites = \App\Models\specialite::whereIn('id', $specialiteIds)
            ->orderBy('nom_specialite')->get(['id','nom_specialite']);

        // Options de scolarité (affichées dans le select)
        $scolarites = $sco->map(function($s){
            return [
                'id'    => $s->id,
                'label' => sprintf(
                    'Niv: %s | Spé: %s | Total: %s',
                    $s->niveaux->nom_niveau ?? '—',
                    $s->specialites->nom_specialite ?? '—',
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

    public function storev(Request $r)
    {
        $r->validate([
            'id_facture_etudiant'                => 'required|integer|exists:facture_etudiants,id',
            'id_etudiant'                        => 'required|integer|exists:etudiants,id',
            'id_annee_academique'                => 'required|integer|exists:annee_academiques,id',

            'type_versement'                     => 'required|in:espece,bancaire,om,mtn',
            'id_caisse'                          => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'                          => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',

            // Budget cascade (obligatoire)
            'id_budget'                          => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree'         => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree' => 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_budgetaire_entree'        => 'required|integer|exists:donnee_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree'  => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',

            // Scolarité : choisir la tranche (ou "toutes" = 0)
            'id_tranche_scolarite'               => 'nullable|integer|exists:tranche_scolarites,id',
            'montant_reglement'                  => 'required|numeric|min:0.01',
            'motif_reglement'                    => 'nullable|string|max:255',
            'date_reglement'                    => 'nullable|string|max:255',
        ], [
            'id_caisse.required_if' => 'La caisse est obligatoire pour un règlement en espèces.',
            'id_banque.required_if' => 'La banque est obligatoire pour un règlement bancaire.',
        ]);

        $facture = facture_etudiant::findOrFail($r->id_facture_etudiant);

        $totalFacture = (float)$facture->montant_total_facture;
        $totalPaye    = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)->sum('montant_reglement');
        $reste        = max(0, $totalFacture - $totalPaye);

        // (Optionnel) Empêcher de dépasser le reste
        if ($r->montant_reglement > $reste) {
            return back()->withInput()->withErrors(['montant_reglement' => "Le montant dépasse le reste à payer (".number_format($reste,0,',',' ').")."]);
        }

        return DB::transaction(function () use ($r, $facture) {
            $numero = $this->generateNumeroReglement((int)$r->id_annee_academique);

            $data = [
                'id_facture_etudiant'                 => (int)$r->id_facture_etudiant,
                'id_etudiant'                         => (int)$r->id_etudiant,
                'id_annee_academique'                 => (int)$r->id_annee_academique,
                'date_reglement'                      => $r->date_reglement,
                'numero_reglement'                    => $numero,

                // type (0=frais, 1=scolarité) repris de la facture
                'type_reglement'                      => (int)$facture->type_facture,

                'type_versement'                      => $r->type_versement,
                'id_caisse'                           => (int)($r->id_caisse ?? 0),
                'id_banque'                           => (int)($r->id_banque ?? 0),

                'id_budget'                           => (int)$r->id_budget,
                'id_ligne_budgetaire_entree'          => (int)$r->id_ligne_budgetaire_entree,
                'id_element_ligne_budgetaire_entree'  => (int)$r->id_element_ligne_budgetaire_entree,
                'id_donnee_budgetaire_entree'         => (int)$r->id_donnee_budgetaire_entree,   // ✅ nouveau
                'id_donnee_ligne_budgetaire_entree'   => (int)$r->id_donnee_ligne_budgetaire_entree, // ✅

                'id_tranche_scolarite'                => (int)($r->id_tranche_scolarite ?? 0),

                'montant_reglement'                   => (float)$r->montant_reglement,
                'motif_reglement'                     => $r->motif_reglement,
                'lettre'                              => $this->toWordsFr((float)$r->montant_reglement).' francs CFA',
                'id_user'                              => Auth::id() ?? 0,
            ];

            reglement_etudiant::create($data);

            return redirect()
                ->route('reglement_by_facture', $facture->id)
                ->with('success', 'Règlement N° '.$numero.' enregistré ✅');
        });
    }

    /** (Optionnel) Modifier un règlement */
    public function updatevo(Request $r)
    {
        // 0=frais, 1=scolarité
        $r->validate([
            'id'                                   => 'required|integer|exists:reglement_etudiants,id',
            'id_facture_etudiant'                  => 'required|integer|exists:facture_etudiants,id',
            'id_etudiant'                          => 'required|integer|exists:etudiants,id',
            'id_annee_academique'                  => 'required|integer|exists:annee_academiques,id',

            // --- PÉDAGOGIE ---
            'id_cycle'                             => 'required|integer|min:1',
            'id_filiere'                           => 'required|integer|min:1',
            'id_niveau'                            => 'nullable|integer|min:0',
            'id_specialite'                        => 'required|integer|min:1',
            'id_scolarite'                         => 'required|integer|exists:scolarites,id',

            // --- TYPE + TRANCHE / FRAIS ---
            'id_tranche_scolarite'                 => 'nullable|integer|min:0',
            'id_frais'                             => 'nullable|integer|min:0',

            // --- VERSEMENT ---
            'type_versement'                       => 'required|in:espece,bancaire,om,mtn',
            'id_caisse'                            => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'                            => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',

            // --- BUDGET ---
            'id_budget'                            => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree'           => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'   => 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_budgetaire_entree'          => 'required|integer|exists:donnee_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree'    => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',

            // --- MONTANT / MOTIF ---
            'montant_reglement'                    => 'required|numeric|min:0.01',
            'motif_reglement'                      => 'nullable|string|max:255',
        ], [
            'id_caisse.required_if' => 'La caisse est obligatoire pour un règlement en espèces.',
            'id_banque.required_if' => 'La banque est obligatoire pour un règlement bancaire.',
        ]);

        $reg = reglement_etudiant::findOrFail($r->id);
        $facture = facture_etudiant::findOrFail($r->id_facture_etudiant);

        // Calcule le reste disponible en excluant CE règlement
        $totalFacture = (float)$facture->montant_total_facture;
        $totalAutres  = (float) reglement_etudiant::where('id_facture_etudiant', $facture->id)
            ->where('id', '<>', $reg->id)
            ->sum('montant_reglement');
        $reste = max(0, $totalFacture - $totalAutres);

        if ($r->montant_reglement > $reste) {
            return back()->withInput()->withErrors([
                'montant_reglement' => "Le montant dépasse le reste disponible (".number_format($reste,0,',',' ').")."
            ]);
        }

        // Map (si colonne INT). Si STRING en base: mets directement $r->type_versement
        $map = ['espece'=>0,'bancaire'=>1,'om'=>2,'mtn'=>3];
        $typeVersementInt = $map[$r->type_versement] ?? 0;

        // Type de règlement calqué sur la facture
        $typeReglement = (int)$facture->type_facture; // 0=frais, 1=scolarité
        $idFrais   = $typeReglement === 0 ? (int)($r->id_frais ?: $facture->id_frais ?? 0) : 0;
        $idTranche = $typeReglement === 1 ? (int)($r->id_tranche_scolarite ?? 0) : 0;

        $reg->update([
            // Liens de base
            'id_facture_etudiant'                 => (int)$r->id_facture_etudiant,
            'id_etudiant'                         => (int)$r->id_etudiant,
            'id_annee_academique'                 => (int)$r->id_annee_academique,
            // 'date_reglement'                     => $reg->date_reglement, // en général on ne touche pas à la date; sinon -> now()

            // Pédagogie
            'id_cycle'                            => (int)$r->id_cycle,
            'id_filiere'                          => (int)$r->id_filiere,
            'id_niveau'                           => (int)($r->id_niveau ?? 0),
            'id_specialite'                       => (int)$r->id_specialite,
            'id_scolarite'                        => (int)$r->id_scolarite,

            // Types
            'type_reglement'                      => $typeReglement,
            'id_tranche_scolarite'                => $idTranche,
            'id_frais'                            => $idFrais,

            // Versement
            'type_versement'                      => $typeVersementInt,                 // ⚠️ INT en base
            // si STRING: 'type_versement'        => $r->type_versement,
            'id_caisse'                           => (int)($r->id_caisse ?? 0),
            'id_banque'                           => (int)($r->id_banque ?? 0),

            // Budget
            'id_budget'                           => (int)$r->id_budget,
            'id_ligne_budgetaire_entree'          => (int)$r->id_ligne_budgetaire_entree,
            'id_element_ligne_budgetaire_entree'  => (int)$r->id_element_ligne_budgetaire_entree,
            'id_donnee_budgetaire_entree'         => (int)$r->id_donnee_budgetaire_entree,
            'id_donnee_ligne_budgetaire_entree'   => (int)$r->id_donnee_ligne_budgetaire_entree,

            // Montant / libellés
            'montant_reglement'                   => (float)$r->montant_reglement,
            'motif_reglement'                     => $r->motif_reglement,
            'lettre'                              => $this->toWordsFr((float)$r->montant_reglement).' francs CFA',

            // Traçabilité (on peut laisser tel quel ; pas forcément changer)
            // 'id_user'                           => Auth::id() ?? $reg->id_user,
        ]);

        return redirect()
            ->route('reglement_by_facture', $facture->id)
            ->with('success', 'Règlement N° '.$reg->numero_reglement.' mis à jour ✅');
    }

    public function updatev(Request $r)
    {
        $r->validate([
            'id'                                  => 'required|integer|exists:reglement_etudiants,id',
            'type_versement'                      => 'required|in:espece,bancaire,om,mtn',
            'id_caisse'                           => 'required_if:type_versement,espece|nullable|integer|exists:caisses,id',
            'id_banque'                           => 'required_if:type_versement,bancaire|nullable|integer|exists:banques,id',
            'id_budget'                           => 'required|integer|exists:budgets,id',
            'id_ligne_budgetaire_entree'          => 'required|integer|exists:ligne_budgetaire_entrees,id',
            'id_element_ligne_budgetaire_entree'  => 'required|integer|exists:element_ligne_budgetaire_entrees,id',
            'id_donnee_budgetaire_entree'         => 'required|integer|exists:donnee_budgetaire_entrees,id',
            'id_donnee_ligne_budgetaire_entree'   => 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',
            'id_tranche_scolarite'                => 'nullable|integer|exists:tranche_scolarites,id',
            'montant_reglement'                   => 'required|numeric|min:0.01',
            'motif_reglement'                     => 'nullable|string|max:255',
        ]);

        $reg = reglement_etudiant::findOrFail($r->id);
        $reg->update([
            'type_versement'                      => $r->type_versement,
            'id_caisse'                           => (int)($r->id_caisse ?? 0),
            'id_banque'                           => (int)($r->id_banque ?? 0),
            'id_budget'                           => (int)$r->id_budget,
            'id_ligne_budgetaire_entree'          => (int)$r->id_ligne_budgetaire_entree,
            'id_element_ligne_budgetaire_entree'  => (int)$r->id_element_ligne_budgetaire_entree,
            'id_donnee_budgetaire_entree'         => (int)$r->id_donnee_budgetaire_entree,
            'id_donnee_ligne_budgetaire_entree'   => (int)$r->id_donnee_ligne_budgetaire_entree,
            'id_tranche_scolarite'                => (int)($r->id_tranche_scolarite ?? 0),
            'montant_reglement'                   => (float)$r->montant_reglement,
            'motif_reglement'                     => $r->motif_reglement,
            'lettre'                              => $this->toWordsFr((float)$r->montant_reglement).' francs CFA',
        ]);

        return back()->with('success', 'Règlement modifié ✏️');
    }

    public function destroy($id)
    {
        reglement_etudiant::findOrFail($id)->delete();
        return back()->with('success', 'Règlement supprimé 🗑️');
    }

    /** Génère un numéro de règlement séquentiel par année académique */
    protected function generateNumeroReglement(int $anneeId): int
    {
        $last = reglement_etudiant::where('id_annee_academique', $anneeId)->max('numero_reglement');
        return (int)$last + 1;
    }

    /** Très simple conversion nombre → mots FR (basique) */
    protected function toWordsFr1(float $n): string
    {
        // Simple : on s’appuie juste sur number_format (tu peux brancher une vraie lib si besoin)
        return trim(rtrim(str_replace(['.', ','], ['',' '], number_format($n, 0, ',', ' '))));
    }
    protected function toWordsFr(float $n): string
    {
        if (!class_exists('\NumberFormatter')) {
            return "Erreur : extension intl non activée";
        }

        $formatter = new \NumberFormatter("fr", \NumberFormatter::SPELLOUT);
        $parts = explode('.', number_format($n, 2, '.', ''));

        $lettres = $formatter->format((int)$parts[0]) . ' francs';
        if ((int)$parts[1] > 0) {
            $lettres .= ' et ' . $formatter->format((int)$parts[1]) . ' centimes';
        }

        return ucfirst($lettres);
    }


    // --------- AJAX PÉDAGOGIE (si besoin d’afficher tranches selon scolarité) ----------
    public function tranchesByScolarite($id)
    {
        $trs = tranche_scolarite::where('id_scolarite', (int)$id)
            ->orderBy('date_limite')
            ->get(['id','nom_tranche','montant_tranche','date_limite']);
        return response()->json($trs);
    }

    // --------- AJAX BUDGET CASCADE ----------
    public function ajaxLignesByBudget($budgetId)
    {
        $lignes = ligne_budgetaire_Entree::orderBy('libelle_ligne_budgetaire_entree')
            ->get(['id','libelle_ligne_budgetaire_entree']);
        return response()->json($lignes);
    }

    public function ajaxElementsByLigne($ligneId)
    {
        $elts = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', (int)$ligneId)
            ->orderBy('libelle_elements_ligne_budgetaire_entree')
            ->get(['id','libelle_elements_ligne_budgetaire_entree']);
        return response()->json($elts);
    }

    /** Ligne + Budget => Données budgétaires d’entrée */
    public function ajaxDonneesBudgetByLigne(Request $r, $ligneId)
    {
        $r->validate(['id_budget' => 'required|integer|min:1']);
        $items = donnee_budgetaire_entree::where('id_ligne_budgetaire_entree', (int)$ligneId)
            ->where('id_budget', (int)$r->id_budget)
            ->orderBy('donnee_ligne_budgetaire_entree') // nom ci-contre dans ton modèle
            ->get(['id','donnee_ligne_budgetaire_entree as label','montant']);
        return response()->json($items);
    }

    /** Élément + Budget + Donnée budgétaire => Données de ligne */
    public function ajaxDonneesLigneByElement(Request $r, $elementId)
    {
        $r->validate([
            'id_budget'                   => 'required|integer|min:1',
            'id_donnee_budgetaire_entree' => 'required|integer|min:1',
        ]);

        $items = donnee_ligne_budgetaire_entree::where('id_element_ligne_budgetaire_entree', (int)$elementId)
            ->where('id_budget', (int)$r->id_budget)
            ->where('id_donnee_budgetaire_entree', (int)$r->id_donnee_budgetaire_entree)
            ->orderBy('donnee_ligne_budgetaire_entree')
            ->get(['id','donnee_ligne_budgetaire_entree as label','montant']);
        return response()->json($items);
    }

    // --------- PDF (facultatif si déjà fait ailleurs) ----------
    public function showPdf($id)
    {
        $reg = reglement_etudiant::with([
            'etudiants','caisse','banque','user',
            'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree',
            'donnee_budgetaire_entree','donnee_ligne_budgetaire_entree',
        ])->findOrFail($id);

        return view('Admin.ReglementEtudiant.pdf', compact('reg'));
    }

    public function downloadPdf($id)
    {
        if (!class_exists(\PDF::class)) {
            return redirect()->route('reglement_pdf', $id);
        }
        $reg = reglement_etudiant::with(['etudiants'])->findOrFail($id);
        $pdf = \PDF::loadView('Admin.ReglementEtudiant.pdf', compact('reg'))
            ->setPaper('a4','portrait');
        return $pdf->download('Reglement_'.$reg->numero_reglement.'.pdf');
    }
}
