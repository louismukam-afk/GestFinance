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

$code = $spec->code_specialite ?? ('SP'.$spec->id); // adapte si besoin

// "2025-2026" → "25"
if (preg_match('/\d{4}/', (string)$annee->nom, $m)) $aa = substr($m[0], -2);
else $aa = date('y');

$prefix = $code.$aa;
$count  = Etudiant::where('matricule', 'LIKE', $prefix.'%')->count() + 1;

return sprintf('%s%04d', $prefix, $count);
}

/** Liste des factures d’un étudiant + form */
public function indexByEtudiant($etudiantId)
{
$etudiant = Etudiant::findOrFail($etudiantId);

$factures = facture_etudiant::with([
'cycles','filieres','niveaux','specialites',
'scolarites','tranche_scolarites','frais',
'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree'
])
->where('id_etudiant', $etudiantId)
->orderBy('date_facture', 'desc')
->get();

$title    = "Factures de l'étudiant : {$etudiant->nom}";
$cycles   = cycle::orderBy('nom_cycle')->get();
$filieres = filiere::orderBy('nom_filiere')->get();
$fraisList= frais::orderBy('nom_frais')->get();
$annees   = annee_academique::orderBy('created_at', 'desc')->get();
$budgets  = budget::orderBy('created_at', 'desc')->get();

return view('Admin.FactureEtudiant.index', compact(
'title','etudiant','factures','cycles','filieres','fraisList','annees','budgets'
));
}

/** AJAX: niveaux/specialites/scolarites depuis cycle+filiere */
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

/** AJAX: tranches par scolarité (affichage uniquement) */
public function tranchesByScolarite($id)
{
$trs = tranche_scolarite::where('id_scolarite', (int)$id)
->orderBy('date_limite')
->get(['id','nom_tranche','montant_tranche','date_limite']);
return response()->json($trs);
}

/** AJAX: Budget → Lignes */
public function lignesByBudget($budgetId)
{
$lignes = ligne_budgetaire_Entree::where('id_user','>=',0) // filtre libre: toutes les lignes (ou par budget si tu as une relation)
->orderBy('libelle_ligne_budgetaire_entree')
->get(['id','libelle_ligne_budgetaire_entree']);
return response()->json($lignes);
}

/** AJAX: Ligne → Éléments */
public function elementsByLigne($ligneId)
{
$elts = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', (int)$ligneId)
->orderBy('libelle_elements_ligne_budgetaire_entree')
->get(['id','libelle_elements_ligne_budgetaire_entree']);
return response()->json($elts);
}

/** AJAX: Élément → Données */
public function donneesByElement($elementId)
{
$dons = donnee_ligne_budgetaire_entree::where('id_element_ligne_budgetaire_entree', (int)$elementId)
->orderBy('donnee_ligne_budgetaire_entree')
->get(['id','donnee_ligne_budgetaire_entree','montant']);
return response()->json($dons);
}

/** Création */
public function store(Request $r)
{
$r->validate([
'id_etudiant'          => 'required|integer|exists:etudiants,id',
'type_facture'         => 'required|in:0,1', // 0=frais, 1=scolarité
'date_facture'         => 'required|date',
'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',

'id_cycle'             => 'required|integer',
'id_filiere'           => 'required|integer',
'id_niveau'            => 'nullable|integer',
'id_specialite'        => 'required|integer',
'id_scolarite'         => 'required|integer|exists:scolarites,id',
// Tranche NON sélectionnée → on force 0
'id_frais'             => 'nullable|integer|exists:frais,id',
'id_tranche_scolarite'             => 'nullable|integer|exists:tranche_scolarites,id',

// Budget cascade (obligatoire)
'id_budget'                        => 'required|integer|exists:budgets,id',
'id_ligne_budgetaire_entree'       => 'required|integer|exists:ligne_budgetaire_entrees,id',
'id_element_ligne_budgetaire_entree'=> 'required|integer|exists:element_ligne_budgetaire_entrees,id',
'id_donnee_ligne_budgetaire_entree'=> 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',
]);

return DB::transaction(function () use ($r) {
$type     = (int) $r->type_facture;
$anneeId  = (int) $r->id_annee_academique;
$etudiant = Etudiant::findOrFail($r->id_etudiant);

// Matricule auto si vide
$newMatricule = $this->generateMatricule($etudiant, (int)$r->id_specialite, $anneeId);
if ($newMatricule && empty($etudiant->matricule)) {
$etudiant->update(['matricule' => $newMatricule]);
}

// Numéro auto
$numero = $this->generateNumeroFacture($anneeId);

// Montant
$idFrais   = 0;
$montant   = 0.0;
$trancheId = 0; // tu as demandé : pas de sélection de tranche

if ($type === 1) { // scolarité
$sc = scolarite::findOrFail($r->id_scolarite);
$montant = (float) $sc->montant_total;
$idFrais = 0;
} else { // frais
$fr = frais::findOrFail((int) $r->id_frais);
$idFrais = (int) $fr->id;
$montant = (float) $fr->montant;
}

$facture = facture_etudiant::create([
'id_etudiant'   => (int)$r->id_etudiant,
'id_cycle'      => (int)$r->id_cycle,
'id_filiere'    => (int)$r->id_filiere,
'id_niveau'     => (int)($r->id_niveau ?? 0),
'id_specialite' => (int)$r->id_specialite,
'type_facture'  => $type,
'id_scolarite'  => (int)$r->id_scolarite,
'id_tranche_scolarite' => $trancheId, // 0
'id_frais'      => $idFrais,          // 0 si scolarité

'id_budget'     => (int)$r->id_budget,
'id_ligne_budgetaire_entree' => (int)$r->id_ligne_budgetaire_entree,
'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,

'numero_facture'=> $numero,
'date_facture'  => $r->date_facture,
'id_annee_academique' => $anneeId,
'montant_total_facture' => $montant,
'id_user'       => Auth::id() ?? 0,
]);

// Génération PDF (2 exemplaires sur A4)
$this->generateInvoicePdf($facture->id);

return back()->with('success', 'Facture enregistrée ✅ (N° '.$numero.')');
});
}

/** Mise à jour (toujours sans tranche) */
public function update(Request $r)
{
$r->validate([
'id'                   => 'required|integer|exists:facture_etudiants,id',
'type_facture'         => 'required|in:0,1',
'date_facture'         => 'required|date',
'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',
'id_cycle'             => 'required|integer',
'id_filiere'           => 'required|integer',
'id_niveau'            => 'nullable|integer',
'id_specialite'        => 'required|integer',
'id_scolarite'         => 'required|integer|exists:scolarites,id',
'id_frais'             => 'nullable|integer|exists:frais,id',
//            'id_tranche_scolarite'             => 'nullable|integer|exists:tranche_scolarites,id',

'id_budget'                        => 'required|integer|exists:budgets,id',
'id_ligne_budgetaire_entree'       => 'required|integer|exists:ligne_budgetaire_entrees,id',
'id_element_ligne_budgetaire_entree'=> 'required|integer|exists:element_ligne_budgetaire_entrees,id',
'id_donnee_ligne_budgetaire_entree'=> 'required|integer|exists:donnee_ligne_budgetaire_entrees,id',
]);

return DB::transaction(function () use ($r) {
$f = facture_etudiant::findOrFail($r->id);
$type = (int) $r->type_facture;

$idFrais = 0;
$montant = 0.0;


/*  if ($type === 1) { // scolarité
$idFrais = 0;
$sc = scolarite::findOrFail($r->id_scolarite);
if ($r->filled('id_tranche_scolarite')) {
$tr = tranche_scolarite::findOrFail($r->id_tranche_scolarite);
$montant = (float) $tr->montant_tranche;
} else {
$montant = (float) $sc->montant_total;
}
} else { // frais
$fr = frais::findOrFail((int) $r->id_frais);
$idFrais = (int) $fr->id;
$montant = (float) $fr->montant;
}*/
if ($type === 1) {
$sc = scolarite::findOrFail($r->id_scolarite);
$montant = (float) $sc->montant_total;
$idFrais = 0;
} else {
$fr = frais::findOrFail((int) $r->id_frais);
$idFrais = (int) $fr->id;
$montant = (float) $fr->montant;
}

$f->update([
'id_cycle'      => (int)$r->id_cycle,
'id_filiere'    => (int)$r->id_filiere,
'id_niveau'     => (int)($r->id_niveau ?? 0),
'id_specialite' => (int)$r->id_specialite,
'type_facture'  => $type,
'id_scolarite'  => (int)$r->id_scolarite,
'id_tranche_scolarite' => 0, // toujours 0
'id_frais'      => $idFrais,
'date_facture'  => $r->date_facture,
'id_annee_academique' => (int)$r->id_annee_academique,
'montant_total_facture' => $montant,

'id_budget'     => (int)$r->id_budget,
'id_ligne_budgetaire_entree' => (int)$r->id_ligne_budgetaire_entree,
'id_element_ligne_budgetaire_entree' => (int)$r->id_element_ligne_budgetaire_entree,
'id_donnee_ligne_budgetaire_entree'  => (int)$r->id_donnee_ligne_budgetaire_entree,
]);

// Regénérer le PDF (mise à jour)
$this->generateInvoicePdf($f->id);

return back()->with('success', 'Facture modifiée ✏️ (N° '.$f->numero_facture.')');
});
}

public function destroy($id)
{
facture_etudiant::findOrFail($id)->delete();
return back()->with('success', 'Facture supprimée 🗑️');
}

/** Génération PDF 2 exemplaires A4 */
protected function generateInvoicePdf(int $factureId): void
{
$f = facture_etudiant::with([
'etudiants','cycles','filieres','niveaux','specialites',
'scolarites','frais',
'budget','ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree'
])->findOrFail($factureId);

// toutes les tranches liées à la scolarité (affichage sur facture)
$tranches = tranche_scolarite::where('id_scolarite', $f->id_scolarite)
->orderBy('date_limite')->get();

$pdf = PDF::loadView('Admin.FactureEtudiant.pdf', [
'f' => $f,
'tranches' => $tranches,
])->setPaper('a4');

$path = "factures/FACT-{$f->numero_facture}.pdf";
Storage::disk('public')->put($path, $pdf->output());
}

// ---------- Impression HTML (deux souches sur A4) ----------
public function showPdf($id)
{
$facture = facture_etudiant::with([
'etudiants','cycles','filieres','niveaux','specialites',
'scolarites.tranche_scolarite','tranche_scolarites','frais','budget',
'ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree'
])->findOrFail($id);

return view('Admin.FactureEtudiant.pdf', [
'facture' => $facture,
'etudiant'=> $facture->etudiants,
'tranches'=> $facture->scolarites?->tranche_scolarite ?? collect(),
]);
}

// ---------- Téléchargement PDF (optionnel, si barryvdh/laravel-dompdf installé) ----------
public function downloadPdf($id)
{
$facture = facture_etudiant::with([
'etudiants','cycles','filieres','niveaux','specialites',
'scolarites.tranche_scolarite','tranche_scolarites','frais','budget',
'ligne_budgetaire_entree','element_ligne_budgetaire_entree','donnee_ligne_budgetaire_entree'
])->findOrFail($id);

if (!class_exists(\PDF::class)) {
// fallback : afficher HTML imprimable
return redirect()->route('facture_pdf', $id);
}

$pdf = \PDF::loadView('Admin.FactureEtudiant.pdf', [
'facture' => $facture,
'etudiant'=> $facture->etudiants,
'tranches'=> $facture->scolarites?->tranche_scolarite ?? collect(),])->setPaper('a4', 'portrait');

$filename = 'Facture_'.$facture->numero_facture.'.pdf';
return $pdf->download($filename);
}

// ---------- AJAX Budget => Lignes ----------
public function ajaxLignesByBudget($budgetId)
{
$lignes =ligne_budgetaire_Entree::whereHas('donnee_ligne_budgetaire_entrees', function($q) use ($budgetId){
$q->where('id_budget', (int)$budgetId);
})->orderBy('libelle_ligne_budgetaire_entree')
->get(['id','libelle_ligne_budgetaire_entree']);
return response()->json($lignes);
}

// ---------- AJAX Ligne => Éléments ----------
public function ajaxElementsByLigne($ligneId)
{
$elts = element_ligne_budgetaire_entree::where('id_ligne_budgetaire_entree', (int)$ligneId)
->orderBy('libelle_elements_ligne_budgetaire_entree')
->get(['id','libelle_elements_ligne_budgetaire_entree']);
return response()->json($elts);
}

// ---------- AJAX Élément (+Budget) => Données ----------
public function ajaxDonneesByElement(Request $r, $elementId)
{
$r->validate(['id_budget' => 'required|integer|min:1']);
$donnees = donnee_ligne_budgetaire_entree::where('id_element_ligne_budgetaire_entree', (int)$elementId)
->where('id_budget', (int)$r->id_budget)
->orderBy('donnee_ligne_budgetaire_entree')
->get(['id', 'donnee_ligne_budgetaire_entree', 'montant']);
return response()->json($donnees);
}
/** Séquence par année académique (N° facture auto) */
/* protected function generateNumeroFacture(int $anneeId): int
{
$last = facture_etudiant::where('id_annee_academique', $anneeId)->max('numero_facture');
return (int) $last + 1;
}

/** Matricule : CODE_SPECIALITE + AA + compteur (0001) */
/* protected function generateMatricule(Etudiant $etudiant, int $specialiteId, int $anneeId): ?string
{
if (!empty($etudiant->matricule)) return $etudiant->matricule;

$spec  = specialite::find($specialiteId);
$annee = annee_academique::find($anneeId);
if (!$spec || !$annee) return null;

$code = $spec->code_specialite ?? ('SP'.$spec->id);        // adapte si champ différent
// si nom de l'année = "2025-2026", on prend "25"
if (preg_match('/\d{4}/', (string)$annee->nom, $m)) {
$aa = substr($m[0], -2);
} else {
$aa = date('y');
}

$prefix = $code.$aa; // ex: INFO25
$count = Etudiant::where('matricule', 'LIKE', $prefix.'%')->count() + 1;

return sprintf('%s%04d', $prefix, $count);
}

/** Liste des factures d’un étudiant + formulaire */
/*  public function indexByEtudiant($etudiantId)
{
$etudiant = Etudiant::findOrFail($etudiantId);

$factures = facture_etudiant::with([
'cycles','filieres','niveaux','specialites','scolarites','tranche_scolarites','frais'
])
->where('id_etudiant', $etudiantId)
->orderBy('date_facture', 'desc')
->get();

$title   = "Factures de l'étudiant : {$etudiant->nom}";
$cycles  = cycle::orderBy('nom_cycle')->get();
$filieres= filiere::orderBy('nom_filiere')->get();
$fraisList = frais::orderBy('nom_frais')->get();
$annees  = annee_academique::orderBy('created_at', 'desc')->get();

return view('Admin.FactureEtudiant.index', compact(
'title','etudiant','factures','cycles','filieres','fraisList','annees'
));
}*/

/** AJAX: niveaux/spécialités/scolarités à partir cycle+filière (via table scolarites) */
/* public function filtersFromCycleFiliere(Request $request)
{
try {
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
return response()->json([
'niveaux' => [], 'specialites' => [], 'scolarites' => [],
]);
}

$niveauIds     = $sco->pluck('id_niveau')->filter()->unique()->values();
$specialiteIds = $sco->pluck('id_specialite')->filter()->unique()->values();

$niveaux = niveau::whereIn('id', $niveauIds)->orderBy('nom_niveau')->get(['id','nom_niveau']);
$specialites = specialite::whereIn('id', $specialiteIds)->orderBy('nom_specialite')->get(['id','nom_specialite']);

$scolarites = $sco->map(function($s) {
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
} catch (\Throwable $e) {
Log::error('filtersFromCycleFiliere failed', [
'msg' => $e->getMessage(), 'line' => $e->getLine(),
'file' => $e->getFile(), 'input' => $request->all()
]);
return response()->json(['niveaux'=>[],'specialites'=>[],'scolarites'=>[],'error'=>true]);
}
}*/

/** AJAX: tranches par scolarité */
/* public function tranchesByScolarite($id)
{
try {
$trs = tranche_scolarite::where('id_scolarite', (int)$id)
->orderBy('date_limite')
->get(['id','nom_tranche','montant_tranche','date_limite']);
return response()->json($trs);
} catch (\Throwable $e) {
Log::error('tranchesByScolarite failed', [
'msg' => $e->getMessage(), 'line' => $e->getLine(),
'file' => $e->getFile(), 'scolarite_id' => $id
]);
return response()->json([]);
}
}

/** Création */
/*
public function store(Request $r)
{
$r->validate([
'id_etudiant'          => 'required|integer|exists:etudiants,id',
'type_facture'         => 'required|in:0,1', // 0=frais, 1=scolarité
'date_facture'         => 'required|date',
'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',
'id_cycle'             => 'required|integer',
'id_filiere'           => 'required|integer',
'id_niveau'            => 'nullable|integer',
'id_specialite'        => 'required|integer',
'id_scolarite'         => 'required|integer|exists:scolarites,id',
'id_tranche_scolarite' => 'nullable|integer|exists:tranche_scolarites,id',
'id_frais'             => 'nullable|integer|exists:frais,id',
]);

return DB::transaction(function () use ($r) {
$type     = (int) $r->type_facture;
$anneeId  = (int) $r->id_annee_academique;
$etudiant = Etudiant::findOrFail($r->id_etudiant);

// Matricule auto si vide
$newMatricule = $this->generateMatricule($etudiant, (int)$r->id_specialite, $anneeId);
if ($newMatricule && empty($etudiant->matricule)) {
$etudiant->update(['matricule' => $newMatricule]);
}

// Numéro de facture auto
$numero = $this->generateNumeroFacture($anneeId);

$idFrais = 0;
$montant = 0.0;

if ($type === 1) { // scolarité
$idFrais = 0;
$sc = scolarite::findOrFail($r->id_scolarite);
if ($r->filled('id_tranche_scolarite')) {
$tr = tranche_scolarite::findOrFail($r->id_tranche_scolarite);
$montant = (float) $tr->montant_tranche;
} else {
$montant = (float) $sc->montant_total;
}
} else { // frais
$fr = frais::findOrFail((int) $r->id_frais);
$idFrais = (int) $fr->id;
$montant = (float) $fr->montant;
}

facture_etudiant::create([
'id_etudiant'   => (int)$r->id_etudiant,
'id_cycle'      => (int)$r->id_cycle,
'id_filiere'    => (int)$r->id_filiere,
'id_niveau'     => (int)($r->id_niveau ?? 0),
'id_specialite' => (int)$r->id_specialite,
'type_facture'  => $type,
'id_scolarite'  => (int)$r->id_scolarite,
'id_tranche_scolarite' => (int)($r->id_tranche_scolarite ?? 0),
'id_frais'      => $idFrais, // 0 si scolarité
'id_budget'     => (int)($r->id_budget ?? 0),
'id_ligne_budgetaire_entree' => (int)($r->id_ligne_budgetaire_entree ?? 0),
'id_element_ligne_budgetaire_entree' => (int)($r->id_element_ligne_budgetaire_entree ?? 0),
'id_donnee_ligne_budgetaire_entree'  => (int)($r->id_donnee_ligne_budgetaire_entree ?? 0),
'numero_facture'=> $numero,
'date_facture'  => $r->date_facture,
'id_annee_academique' => $anneeId,
'montant_total_facture' => $montant,
'id_user'       => Auth::id() ?? 0,
]);

return back()->with('success', 'Facture enregistrée ✅ (N° '.$numero.')');
});
}*/

/** Mise à jour */
/* public function update(Request $r)
{
$r->validate([
'id'                   => 'required|integer|exists:facture_etudiants,id',
'type_facture'         => 'required|in:0,1',
'date_facture'         => 'required|date',
'id_annee_academique'  => 'required|integer|exists:annee_academiques,id',
'id_cycle'             => 'required|integer',
'id_filiere'           => 'required|integer',
'id_niveau'            => 'nullable|integer',
'id_specialite'        => 'required|integer',
'id_scolarite'         => 'required|integer|exists:scolarites,id',
'id_tranche_scolarite' => 'nullable|integer|exists:tranche_scolarites,id',
'id_frais'             => 'nullable|integer|exists:frais,id',
]);

return DB::transaction(function () use ($r) {
$f = facture_etudiant::findOrFail($r->id);
$type = (int) $r->type_facture;

$idFrais = 0;
$montant = 0.0;

if ($type === 1) { // scolarité
$idFrais = 0;
$sc = scolarite::findOrFail($r->id_scolarite);
if ($r->filled('id_tranche_scolarite')) {
$tr = tranche_scolarite::findOrFail($r->id_tranche_scolarite);
$montant = (float) $tr->montant_tranche;
} else {
$montant = (float) $sc->montant_total;
}
} else { // frais
$fr = frais::findOrFail((int) $r->id_frais);
$idFrais = (int) $fr->id;
$montant = (float) $fr->montant;
}

$f->update([
'id_cycle'      => (int)$r->id_cycle,
'id_filiere'    => (int)$r->id_filiere,
'id_niveau'     => (int)($r->id_niveau ?? 0),
'id_specialite' => (int)$r->id_specialite,
'type_facture'  => $type,
'id_scolarite'  => (int)$r->id_scolarite,
'id_tranche_scolarite' => (int)($r->id_tranche_scolarite ?? 0),
'id_frais'      => $idFrais,
'date_facture'  => $r->date_facture,
'id_annee_academique' => (int)$r->id_annee_academique,
'montant_total_facture' => $montant,
]);

return back()->with('success', 'Facture modifiée ✏️ (N° '.$f->numero_facture.')');
});
}*/

/** Suppression */
/* public function destroy($id)
{
facture_etudiant::findOrFail($id)->delete();
return back()->with('success', 'Facture supprimée 🗑️');
}*/

/** (Optionnel) Étudiants avec factures */
/* public function etudiantsAvecFactures()
{
$etudiants = Etudiant::whereIn('id', function($q) {
$q->select('id_etudiant')->from('facture_etudiants')->groupBy('id_etudiant');
})->withCount('facture_etudiants')->orderByDesc('facture_etudiants_count')->get();

$title = "Étudiants avec facture(s)";
return view('Admin.FactureEtudiant.etudiants_factures', compact('etudiants','title'));
}*/
/*  // Tableau des factures d’un étudiant
public function indexByEtudiant($etudiantId)
{
$etudiant = Etudiant::findOrFail($etudiantId);
$factures = facture_etudiant::with(['cycles','filieres','niveaux','specialites','scolarites','tranche_scolarites','frais'])
->where('id_etudiant', $etudiantId)
->orderBy('date_facture', 'desc')
->get();

$title = "Factures de l'étudiant : {$etudiant->nom}";

// listes de base pour le formulaire
$cycles = cycle::orderBy('nom_cycle')->get();
$filieres = filiere::orderBy('nom_filiere')->get();
$niveaux = collect();      // remplis par AJAX après choix cycle/filiere
$specialites = collect();  // idem
$scolarites = collect();   // idem
$tranches = collect();     // idem
$fraisList = frais::orderBy('nom_frais')->get();
$annees = annee_academique::orderBy('created_at', 'desc')->get();

return view('Admin.FactureEtudiant.index', compact(
'title','etudiant','factures',
'cycles','filieres','niveaux','specialites','scolarites','tranches',
'fraisList','annees'
));
}

// AJAX: retourne niveaux, spécialités, scolarités selon cycle+filiere
public function filtersFromCycleFiliere(Request $request)
{
$request->validate([
'id_cycle'  => 'required|integer',
'id_filiere'=> 'required|integer',
]);

$id_cycle = (int) $request->id_cycle;
$id_filiere = (int) $request->id_filiere;

$sco = scolarite::where('id_cycle', $id_cycle)
->where('id_filiere', $id_filiere)
->get();

// Dériver niveaux / spécialités uniques à partir de scolarites
$niveauIds = $sco->pluck('id_niveau')->unique()->filter();
$specialiteIds = $sco->pluck('id_specialite')->unique()->filter();

$niveaux = niveau::whereIn('id', $niveauIds)->orderBy('nom_niveau')->get(['id','nom_niveau']);
$specialites = specialite::whereIn('id', $specialiteIds)->orderBy('nom_specialite')->get(['id','nom_specialite']);

// renvoyer toutes les scolarites correspondantes (pour choix direct)
$scolarites = $sco->map(function($s) {
return [
'id' => $s->id,
'label' => 'Niv: '.($s->niveaux->nom_niveau ?? 'N/A').' | Spé: '.($s->specialites->nom_specialite ?? 'N/A').' | Montant: '.number_format($s->montant_total,0,',',' ')
];
});

return response()->json([
'niveaux' => $niveaux,
'specialites' => $specialites,
'scolarites' => $scolarites,
]);
}

// AJAX: tranches par scolarite
public function tranchesByScolarite($id)
{
$trs = tranche_scolarite::where('id_scolarite', $id)->orderBy('date_limite')->get(['id','nom_tranche','montant_tranche','date_limite']);
return response()->json($trs);
}

// Création facture
public function store(Request $r)
{
$r->validate([
'id_etudiant'       => 'required|integer|exists:etudiants,id',
'type_facture'      => 'required|in:0,1', // 0 = frais, 1 = scolarité
'numero_facture'    => 'required|integer',
'date_facture'      => 'required|date',
'id_annee_academique' => 'required|integer|exists:annee_academiques,id',

// communs pour construire le contexte
'id_cycle'          => 'required|integer',
'id_filiere'        => 'required|integer',

// si scolarité
'id_scolarite'      => 'required_if:type_facture,1|integer',
'id_tranche_scolarite' => 'nullable|integer',

// si frais
'id_frais'          => 'required_if:type_facture,0|integer',

// montant total (si tu veux forcer calcul côté serveur, garde numeric|min:0 sinon)
'montant_total_facture' => 'nullable|numeric|min:0',
], [
'id_scolarite.required_if' => 'Veuillez choisir la scolarité pour une facture de scolarité.',
'id_frais.required_if'     => 'Veuillez choisir un frais pour une facture de frais.',
]);

// sécurités
$type = (int) $r->type_facture;
$montant = (float) ($r->montant_total_facture ?? 0);

if ($type === 1 && $montant <= 0 && $r->id_scolarite) {
// Par défaut, si pas de montant fourni pour scolarité → on utilise le montant scolarité (ou tranche si fournie)
$sc = scolarite::find($r->id_scolarite);
if ($sc) {
if ($r->id_tranche_scolarite) {
$tr = tranche_scolarite::find($r->id_tranche_scolarite);
$montant = $tr ? (float) $tr->montant_tranche : (float) $sc->montant_total;
} else {
$montant = (float) $sc->montant_total;
}
}
}

facture_etudiant::create([
'id_etudiant'   => $r->id_etudiant,
'id_cycle'      => $r->id_cycle,
'id_filiere'    => $r->id_filiere,
'id_niveau'     => $r->id_niveau ?? 0,
'id_specialite' => $r->id_specialite ?? 0,
'type_facture'  => $type,
'id_scolarite'  => $type === 1 ? ($r->id_scolarite ?? 0) : 0,
'id_tranche_scolarite' => $type === 1 ? ($r->id_tranche_scolarite ?? 0) : 0,
'id_frais'      => $type === 0 ? ($r->id_frais ?? 0) : 0,
'id_budget'     => $r->id_budget ?? 0,
'id_ligne_budgetaire_entree' => $r->id_ligne_budgetaire_entree ?? 0,
'id_element_ligne_budgetaire_entree' => $r->id_element_ligne_budgetaire_entree ?? 0,
'id_donnee_ligne_budgetaire_entree'  => $r->id_donnee_ligne_budgetaire_entree ?? 0,
'numero_facture'=> $r->numero_facture,
'date_facture'  => $r->date_facture,
'id_annee_academique' => $r->id_annee_academique,
'montant_total_facture' => $montant,
'id_user'       => Auth::id() ?? 0,
]);

return back()->with('success', 'Facture enregistrée ✅');
}

// Mise à jour
public function update(Request $r)
{
$r->validate([
'id'                => 'required|integer|exists:facture_etudiants,id',
'type_facture'      => 'required|in:0,1',
'numero_facture'    => 'required|integer',
'date_facture'      => 'required|date',
'id_annee_academique' => 'required|integer|exists:annee_academiques,id',
'id_cycle'          => 'required|integer',
'id_filiere'        => 'required|integer',
'id_scolarite'      => 'required_if:type_facture,1|integer',
'id_tranche_scolarite' => 'nullable|integer',
'id_frais'          => 'required_if:type_facture,0|integer',
'montant_total_facture' => 'nullable|numeric|min:0',
]);

$f = facture_etudiant::findOrFail($r->id);

$type = (int) $r->type_facture;
$montant = (float) ($r->montant_total_facture ?? 0);

// même logique de sécurité que store
if ($type === 1 && $montant <= 0 && $r->id_scolarite) {
$sc = scolarite::find($r->id_scolarite);
if ($sc) {
if ($r->id_tranche_scolarite) {
$tr = tranche_scolarite::find($r->id_tranche_scolarite);
$montant = $tr ? (float) $tr->montant_tranche : (float) $sc->montant_total;
} else {
$montant = (float) $sc->montant_total;
}
}
}

$f->update([
'id_cycle'      => $r->id_cycle,
'id_filiere'    => $r->id_filiere,
'id_niveau'     => $r->id_niveau ?? 0,
'id_specialite' => $r->id_specialite ?? 0,
'type_facture'  => $type,
'id_scolarite'  => $type === 1 ? ($r->id_scolarite ?? 0) : 0,
'id_tranche_scolarite' => $type === 1 ? ($r->id_tranche_scolarite ?? 0) : 0,
'id_frais'      => $type === 0 ? ($r->id_frais ?? 0) : 0,
'numero_facture'=> $r->numero_facture,
'date_facture'  => $r->date_facture,
'id_annee_academique' => $r->id_annee_academique,
'montant_total_facture' => $montant,
]);

return back()->with('success', 'Facture modifiée ✏️');
}

public function destroy($id)
{
facture_etudiant::findOrFail($id)->delete();
return back()->with('success', 'Facture supprimée 🗑️');
}

// Liste d’étudiants qui ont au moins 1 facture
public function etudiantsAvecFactures()
{
$etudiants = Etudiant::whereIn('id', function($q) {
$q->select('id_etudiant')->from('facture_etudiants')->groupBy('id_etudiant');
})->withCount('facture_etudiants')->orderByDesc('facture_etudiants_count')->get();

$title = "Étudiants avec facture(s)";
return view('Admin.FactureEtudiant.etudiants_factures', compact('etudiants','title'));
}*/