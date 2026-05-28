<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Bon_commandeController;
use App\Http\Controllers\Admin\ElementBonCommandeController;
use App\Exports\BonsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Admin\EtatBonCommandeController;
use App\Http\Controllers\Admin\ReglementEtudiantController;
use App\Http\Controllers\Admin\TrancheScolariteController;
use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\Budget\LigneBudgetaireEntreeController;
use App\Http\Controllers\Budget\DecaissementController;
use App\Http\Controllers\Budget\LigneBudgetaireSortieController;
use App\Http\Controllers\Budget\ElementLigneBudgetaireSortieController;
use App\Http\Controllers\Budget\ElementLigneBudgetaireEntreeController;
use App\Http\Controllers\Budget\DonneeBudgetaireSortieController;
use App\Http\Controllers\Budget\DonneeBudgetaireEntreeController;
use App\Http\Controllers\Budget\DonneeLigneBudgetaireEntreeController;
use App\Http\Controllers\Budget\DonneeLigneBudgetaireSortieController;
use App\Http\Controllers\Budget\EtatSortieController;
use App\Http\Controllers\Budget\RetourCaisseController;
use App\Http\Controllers\Budget\EntreeSpecialeController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MesBonCommandeController;
use App\Http\Controllers\Admin\BonValidationController;
use App\Http\Controllers\Admin\EtatEtudiantScolariteController;
use App\Http\Controllers\Admin\ReportingController;
use App\Http\Controllers\Admin\ReductionFactureController;
use App\Http\Controllers\Admin\MatiereController;
use App\Http\Controllers\Admin\FactureRattrapageController;
use App\Http\Controllers\Admin\ProgrammeSpecialiteController;
use App\Http\Controllers\Admin\GroupeMatiereController;
use App\Http\Controllers\Admin\EmploiTempsController;
use App\Http\Controllers\Admin\SalleController;
use App\Http\Controllers\Admin\TauxHoraireController;
use App\Http\Controllers\Admin\PlageHoraireController;
use App\Http\Controllers\Admin\CoursEnseignantController;
use App\Http\Controllers\Admin\BiometrieHeureController;
use App\Http\Controllers\Admin\SalairePermanentController;
use App\Http\Controllers\Admin\DisciplinePersonnelController;
use App\Http\Controllers\Admin\PersonnelEntiteController;
use App\Http\Controllers\Admin\EmploiPermanentController;
use App\Http\Controllers\Admin\PaiePermanentController;

use App\Http\Controllers\Admin\EtatComptableController;
use App\Models\facture_etudiant;
use App\Models\donnee_budgetaire_entree;
use App\Models\donnee_ligne_budgetaire_entree;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

# 🚀 Page d’accueil → redirige vers login
Route::get('/', function () {
    return redirect()->route('login');
});

# 🚀 Auth routes générées par "php artisan make:auth"
Auth::routes();

# 🚀 Toutes les routes protégées
Route::middleware(['auth'])->group(function () {

    # Tableau de bord après login
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
        ->name('home');

    # Tableau de bord admin
    Route::get('/admin/dashboard', function () {
        return view('Admin.index')->with(['title' => 'Administration']);
    })->name('dashboard');

    Route::prefix('etat-etudiants-scolarite')->name('etat_etudiants_scolarite.')->group(function () {
        Route::get('/', [EtatEtudiantScolariteController::class, 'index'])->name('index');
        Route::get('/data', [EtatEtudiantScolariteController::class, 'data'])->name('data');
        Route::get('/export/pdf', [EtatEtudiantScolariteController::class, 'exportPdf'])->name('pdf');
        Route::get('/export/excel', [EtatEtudiantScolariteController::class, 'exportExcel'])->name('excel');
        Route::get('/ajax/budget/{budget}/lignes', [EtatEtudiantScolariteController::class, 'lignesBudgetaires'])->name('ajax.lignes');
        Route::get('/ajax/ligne/{ligne}/elements', [EtatEtudiantScolariteController::class, 'elementsBudgetaires'])->name('ajax.elements');
        Route::get('/ajax/ligne/{ligne}/donnees-budget', [EtatEtudiantScolariteController::class, 'donneesBudgetParLigne'])->name('ajax.donnees_budget');
        Route::get('/ajax/element/{element}/donnees', [EtatEtudiantScolariteController::class, 'donneesBudgetaires'])->name('ajax.donnees');
        Route::get('/ajax/scolarites', [EtatEtudiantScolariteController::class, 'scolarites'])->name('ajax.scolarites');
        Route::get('/ajax/scolarite/{scolarite}/tranches', [EtatEtudiantScolariteController::class, 'tranches'])->name('ajax.tranches');
    });

    Route::prefix('reductions-factures')->name('reductions_factures.')->group(function () {
        Route::get('/', [ReductionFactureController::class, 'index'])->name('index');
        Route::post('/', [ReductionFactureController::class, 'store'])->name('store');
        Route::put('/{id}', [ReductionFactureController::class, 'update'])->name('update');
        Route::delete('/{id}', [ReductionFactureController::class, 'destroy'])->name('destroy');
        Route::get('/export/excel', [ReductionFactureController::class, 'exportExcel'])->name('excel');
        Route::get('/export/pdf', [ReductionFactureController::class, 'exportPdf'])->name('pdf');
    });

    Route::resource('matieres', MatiereController::class)->except(['create', 'show', 'edit']);

    Route::get('/emploi-du-temps', [EmploiTempsController::class, 'index'])->name('emploi_temps.index');
    Route::resource('emploi-du-temps/salles', SalleController::class)->except(['show', 'create', 'edit'])->names('salles');
    Route::resource('emploi-du-temps/taux-horaires', TauxHoraireController::class)->except(['show', 'create', 'edit'])->names('taux_horaires');
    Route::resource('emploi-du-temps/salaires-permanents', SalairePermanentController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['salaires-permanents' => 'salaire_permanent'])
        ->names('salaires_permanents');
    Route::get('emploi-du-temps/discipline-personnels', [DisciplinePersonnelController::class, 'index'])->name('discipline_personnels.index');
    Route::put('emploi-du-temps/discipline-personnels/{discipline}', [DisciplinePersonnelController::class, 'update'])->name('discipline_personnels.update');
    Route::post('emploi-du-temps/discipline-personnels/{discipline}/justifier', [DisciplinePersonnelController::class, 'justify'])->name('discipline_personnels.justify');
    Route::resource('emploi-du-temps/personnel-entites', PersonnelEntiteController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['personnel-entites' => 'personnel_entite'])
        ->names('personnel_entites');
    Route::resource('emploi-du-temps/emplois-permanents', EmploiPermanentController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['emplois-permanents' => 'emploi_permanent'])
        ->names('emplois_permanents');
    Route::resource('emploi-du-temps/plages-horaires', PlageHoraireController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['plages-horaires' => 'plage_horaire'])
        ->names('plages_horaires');
    Route::get('emploi-du-temps/cours-enseignants/contextes', [CoursEnseignantController::class, 'contextes'])->name('cours_enseignants.contextes');
    Route::get('emploi-du-temps/cours-enseignants/specialites/{specialite}/contexte', [CoursEnseignantController::class, 'configure'])->name('cours_enseignants.configure');
    Route::get('emploi-du-temps/cours-enseignants/specialites/{specialite}/create', [CoursEnseignantController::class, 'createForContext'])->name('cours_enseignants.create_context');
    Route::get('emploi-du-temps/emplois-specialites', [CoursEnseignantController::class, 'emploiSpecialite'])->name('cours_enseignants.emploi_specialite');
    Route::get('emploi-du-temps/emplois-specialites/pdf', [CoursEnseignantController::class, 'emploiSpecialitePdf'])->name('cours_enseignants.emploi_specialite_pdf');
    Route::get('emploi-du-temps/emplois-enseignants', [CoursEnseignantController::class, 'emploiEnseignant'])->name('cours_enseignants.emploi_enseignant');
    Route::get('emploi-du-temps/emplois-enseignants/pdf', [CoursEnseignantController::class, 'emploiEnseignantPdf'])->name('cours_enseignants.emploi_enseignant_pdf');
    Route::get('emploi-du-temps/volumes-specialites', [CoursEnseignantController::class, 'volumesSpecialite'])->name('cours_enseignants.volumes_specialite');
    Route::get('emploi-du-temps/volumes-specialites/pdf', [CoursEnseignantController::class, 'volumesSpecialitePdf'])->name('cours_enseignants.volumes_specialite_pdf');
    Route::get('emploi-du-temps/decompte-heures', [BiometrieHeureController::class, 'index'])->name('biometrie_heures.index');
    Route::get('emploi-du-temps/biometrie-permanents', [BiometrieHeureController::class, 'permanentIndex'])->name('biometrie_permanents.index');
    Route::post('emploi-du-temps/biometrie-permanents/import', [BiometrieHeureController::class, 'permanentStore'])->name('biometrie_permanents.store');
    Route::post('emploi-du-temps/biometrie-permanents/{import}/consolider', [BiometrieHeureController::class, 'permanentConsolider'])->name('biometrie_permanents.consolider');
    Route::delete('emploi-du-temps/biometrie-permanents/{import}/consolidation', [BiometrieHeureController::class, 'permanentClear'])->name('biometrie_permanents.clear');
    Route::prefix('emploi-du-temps/paie-permanents')->name('paie_permanents.')->group(function () {
        Route::get('/', [PaiePermanentController::class, 'index'])->name('index');
        Route::post('/generer', [PaiePermanentController::class, 'generer'])->name('generer');
        Route::get('/bulletins/pdf', [PaiePermanentController::class, 'exportPdf'])->name('bulletins.pdf');
        Route::post('/rubriques', [PaiePermanentController::class, 'storeRubrique'])->name('rubriques.store');
        Route::post('/configs', [PaiePermanentController::class, 'storeConfig'])->name('configs.store');
        Route::post('/acomptes', [PaiePermanentController::class, 'storeAcompte'])->name('acomptes.store');
        Route::post('/sanctions', [PaiePermanentController::class, 'storeSanction'])->name('sanctions.store');
        Route::post('/baremes-irpp', [PaiePermanentController::class, 'storeBaremeIrpp'])->name('baremes_irpp.store');
        Route::put('/baremes-irpp/{bareme}', [PaiePermanentController::class, 'updateBaremeIrpp'])->name('baremes_irpp.update');
        Route::delete('/baremes-irpp/{bareme}', [PaiePermanentController::class, 'destroyBaremeIrpp'])->name('baremes_irpp.destroy');
        Route::post('/parametres-cac', [PaiePermanentController::class, 'storeParametreCac'])->name('parametres_cac.store');
        Route::put('/parametres-cac/{cac}', [PaiePermanentController::class, 'updateParametreCac'])->name('parametres_cac.update');
        Route::delete('/parametres-cac/{cac}', [PaiePermanentController::class, 'destroyParametreCac'])->name('parametres_cac.destroy');
        Route::post('/parametres-pvid', [PaiePermanentController::class, 'storeParametrePvid'])->name('parametres_pvid.store');
        Route::put('/parametres-pvid/{pvid}', [PaiePermanentController::class, 'updateParametrePvid'])->name('parametres_pvid.update');
        Route::delete('/parametres-pvid/{pvid}', [PaiePermanentController::class, 'destroyParametrePvid'])->name('parametres_pvid.destroy');
        Route::post('/etats', [PaiePermanentController::class, 'genererEtatPaie'])->name('etats.generer');
        Route::post('/etats/{etat}/regenerer', [PaiePermanentController::class, 'regenererEtatPaie'])->name('etats.regenerer');
        Route::delete('/etats/{etat}', [PaiePermanentController::class, 'destroyEtatPaie'])->name('etats.destroy');
        Route::get('/etats/{etat}', [PaiePermanentController::class, 'showEtatPaie'])->name('etats.show');
        Route::get('/etats/{etat}/pdf', [PaiePermanentController::class, 'exportEtatPaiePdf'])->name('etats.pdf');
        Route::post('/bulletins/valider-global', [PaiePermanentController::class, 'validerGlobal'])->name('bulletins.valider_global');
        Route::post('/bulletins/{bulletin}/valider', [PaiePermanentController::class, 'valider'])->name('bulletins.valider');
    });
    Route::get('emploi-du-temps/decompte-heures/saisie-manuelle', [BiometrieHeureController::class, 'manualCreate'])->name('biometrie_heures.manual.create');
    Route::post('emploi-du-temps/decompte-heures/saisie-manuelle', [BiometrieHeureController::class, 'manualStore'])->name('biometrie_heures.manual.store');
    Route::post('emploi-du-temps/decompte-heures/import', [BiometrieHeureController::class, 'store'])->name('biometrie_heures.store');
    Route::post('emploi-du-temps/decompte-heures/{import}/consolider', [BiometrieHeureController::class, 'consolider'])->name('biometrie_heures.consolider');
    Route::delete('emploi-du-temps/decompte-heures/{import}/consolidation', [BiometrieHeureController::class, 'clearConsolidation'])->name('biometrie_heures.clear');
    Route::delete('emploi-du-temps/decompte-heures/{import}', [BiometrieHeureController::class, 'destroy'])->name('biometrie_heures.destroy');
    Route::post('emploi-du-temps/decompte-heures/association', [BiometrieHeureController::class, 'storeMapping'])->name('biometrie_heures.mapping');
    Route::get('emploi-du-temps/decompte-heures/export/excel', [BiometrieHeureController::class, 'exportExcel'])->name('biometrie_heures.excel');
    Route::get('emploi-du-temps/decompte-heures/export/pdf', [BiometrieHeureController::class, 'exportPdf'])->name('biometrie_heures.pdf');
    Route::get('emploi-du-temps/cours-enseignants/export', [CoursEnseignantController::class, 'export'])->name('cours_enseignants.export');
    Route::resource('emploi-du-temps/cours-enseignants', CoursEnseignantController::class)->except(['show'])->names('cours_enseignants');
    Route::get('/emploi-du-temps/programmes-specialites', [ProgrammeSpecialiteController::class, 'index'])->name('programmes_specialites.index');
    Route::get('/specialites/{specialite}/programme/contexte', [ProgrammeSpecialiteController::class, 'configure'])->name('programmes_specialites.configure');
    Route::get('/specialites/{specialite}/programme', [ProgrammeSpecialiteController::class, 'edit'])->name('programmes_specialites.edit');
    Route::post('/specialites/{specialite}/programme', [ProgrammeSpecialiteController::class, 'store'])->name('programmes_specialites.store');
    Route::put('/programmes-specialites/{programme}', [ProgrammeSpecialiteController::class, 'update'])->name('programmes_specialites.update');
    Route::delete('/programmes-specialites/{programme}', [ProgrammeSpecialiteController::class, 'destroy'])->name('programmes_specialites.destroy');
    Route::get('/programmes-specialites/{programme}/groupe-matiere', [GroupeMatiereController::class, 'create'])->name('groupes_matieres.create');
    Route::post('/programmes-specialites/{programme}/groupe-matiere', [GroupeMatiereController::class, 'store'])->name('groupes_matieres.store');
    Route::delete('/groupes-matieres/{groupe}', [GroupeMatiereController::class, 'destroy'])->name('groupes_matieres.destroy');
    Route::delete('/groupe-matiere-lignes/{ligne}', [GroupeMatiereController::class, 'destroyLine'])->name('groupes_matieres.lignes.destroy');

    Route::prefix('factures-rattrapage')->name('factures_rattrapage.')->group(function () {
        Route::get('/', [FactureRattrapageController::class, 'index'])->name('index');
        Route::get('/create', [FactureRattrapageController::class, 'create'])->name('create');
        Route::get('/create/from-facture/{facture}', [FactureRattrapageController::class, 'createFromFacture'])->name('create_from_facture');
        Route::post('/', [FactureRattrapageController::class, 'store'])->name('store');
        Route::get('/{id}', [FactureRattrapageController::class, 'show'])->name('show');
        Route::delete('/{id}', [FactureRattrapageController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mes-bons')->name('mes_bons.')->group(function () {
        Route::get('/attente', [MesBonCommandeController::class, 'attente'])->name('attente');
        Route::get('/valides', [MesBonCommandeController::class, 'valides'])->name('valides');
        Route::get('/refuses', [MesBonCommandeController::class, 'refuses'])->name('refuses');
        Route::post('/', [MesBonCommandeController::class, 'store'])->name('store');
        Route::put('/{bon}', [MesBonCommandeController::class, 'update'])->name('update');
        Route::delete('/{bon}', [MesBonCommandeController::class, 'destroy'])->name('destroy');
        Route::post('/{bon}/validation-emetteur', [MesBonCommandeController::class, 'validerEmetteur'])->name('valider_emetteur');
        Route::post('/{bon}/refus-emetteur', [MesBonCommandeController::class, 'refuserEmetteur'])->name('refuser_emetteur');
        Route::get('/export/pdf/{type}', [MesBonCommandeController::class, 'exportPdf'])->name('pdf');
        Route::get('/{bon}/document', [MesBonCommandeController::class, 'document'])->name('document');
        Route::get('/{bon}/document/pdf', [MesBonCommandeController::class, 'documentPdf'])->name('document_pdf');


         Route::get('/{bon}/document1', [MesBonCommandeController::class, 'document1'])->name('document1');
        Route::get('/{bon}/document/pdf1', [MesBonCommandeController::class, 'documentPdf1'])->name('document_pdf1');
    });

    Route::prefix('validation-bons')->name('validation_bons.')->group(function () {
        Route::get('/pdg', [BonValidationController::class, 'pdg'])->name('pdg');
        Route::get('/daf', [BonValidationController::class, 'daf'])->name('daf');
        Route::get('/achats', [BonValidationController::class, 'achats'])->name('achats');
        Route::get('/{niveau}', [BonValidationController::class, 'index'])->name('index');
        Route::post('/daf/{bon}/imputer', [BonValidationController::class, 'imputerDaf'])->name('daf.imputer');
        Route::post('/{niveau}/{bon}/valider', [BonValidationController::class, 'valider'])->name('valider');
        Route::post('/{niveau}/{bon}/refuser', [BonValidationController::class, 'refuser'])->name('refuser');
        Route::get('/{niveau}/export/pdf', [BonValidationController::class, 'exportPdf'])->name('pdf');
    });

    Route::prefix('access-control')->name('access.')->middleware('super.admin')->group(function () {
        Route::get('/', [AccessControlController::class, 'index'])->name('index');
        Route::post('/sync', [AccessControlController::class, 'syncRoutes'])->name('sync');
        Route::post('/roles', [AccessControlController::class, 'storeRole'])->name('roles.store');
        Route::get('/roles/{role}/permissions', [AccessControlController::class, 'editRolePermissions'])->name('roles.permissions.edit');
        Route::post('/roles/{role}/permissions', [AccessControlController::class, 'updateRolePermissions'])->name('roles.permissions');
        Route::post('/users/{user}/roles', [AccessControlController::class, 'updateUserRoles'])->name('users.roles');
    });

    Route::prefix('audit')->name('audit.')->middleware('super.admin')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
    });

    # Routes ADMIN protégées
    Route::prefix('admin')->group(function () {

        // 📌 Liste des bons
        Route::get('bon_commande', [Bon_commandeController::class, 'index_bon'])
            ->name('bon_commande_management');

        // 📌 Ajouter un bon
        Route::post('bon_commande/store', [Bon_commandeController::class, 'store'])
            ->name('store_bon_commande');

        // 📌 Modifier un bon
        Route::post('bon_commande/update', [Bon_commandeController::class, 'update'])
            ->name('update_bon_commande');

        // 📌 Supprimer un bon
        Route::delete('bon_commande/{id}', [Bon_commandeController::class, 'destroy'])
            ->name('delete_bon_commande');

        // 📌 Recherche
        Route::get('/bons/search', [Bon_commandeController::class, 'search_bon'])
            ->name('bon_commande_search');

        // 📌 Validations spécifiques
        Route::post('bon_commande/{id}/valider-daf', [Bon_commandeController::class, 'validerDAF'])
            ->name('valider_daf_bon');
        Route::post('bon_commande/{id}/valider-pdf', [Bon_commandeController::class, 'validerPDG'])
            ->name('valider_pdg_bon');
        Route::post('bon_commande/{id}/valider-achats', [Bon_commandeController::class, 'validerAchats'])
            ->name('valider_achats_bon');
        Route::post('bon_commande/{id}/valider-emetteur', [Bon_commandeController::class, 'validerEmetteur'])
            ->name('valider_emetteur_bon');
        Route::post('bon_commande/{niveau}/{bon}/refuser', [BonValidationController::class, 'refuser'])
            ->name('refuser_bon');

        // 📌 Gestion des éléments de bon
        Route::get('/bon/{bon}/elements/manage', [ElementBonCommandeController::class, 'manage'])
            ->name('element_bon.manage');
        Route::get('/bon/{bon}/elements/create', [ElementBonCommandeController::class, 'create'])
            ->name('element_bon.create');
        Route::post('/bon/{bon}/elements/build', [ElementBonCommandeController::class, 'buildForm'])
            ->name('element_bon.buildForm');
        Route::post('/bon/{bon}/elements/store', [ElementBonCommandeController::class, 'store'])
            ->name('element_bon.store');
        Route::get('/bon/{bon}/elements/edit', [ElementBonCommandeController::class, 'editForm'])
            ->name('element_bon.editForm');
        Route::put('/bon/{bon}/elements/update', [ElementBonCommandeController::class, 'updateAll'])
            ->name('element_bon.updateAll');
        Route::delete('/element/{id}', [ElementBonCommandeController::class, 'destroy'])
            ->name('element_bon.destroy');
        Route::get('/bon/{bon}/elements', [ElementBonCommandeController::class, 'index'])
            ->name('element_bon.index');
        Route::get('/bon/{bon}/elements/pdf', [ElementBonCommandeController::class, 'exportPdf'])
            ->name('element_bon.exportPdf');
    });

    # Export Excel (protégé aussi)
    Route::get('admin/bon_commande/export', function () {
        return Excel::download(new BonsExport, 'bons_commandes.xlsx');
    })->name('export_bons');

    // Etat des bons de commande
  /*  Route::prefix('admin/etat-bons')->group(function () {
        Route::get('/', [EtatBonCommandeController::class, 'index'])->name('etat_bons.index');
        Route::get('/export-excel', [EtatBonCommandeController::class, 'exportExcel'])->name('etat_bons.exportExcel');
        Route::get('/export-pdf', [EtatBonCommandeController::class, 'exportPdf'])->name('etat_bons.exportPdf');
        Route::get('/{id}', [EtatBonCommandeController::class, 'show'])->name('etat_bons.show');
        Route::get('/{id}/pdf', [EtatBonCommandeController::class, 'exportBonPdf'])->name('etat_bons.exportBonPdf');
    });*/

    Route::prefix('etat-bons')->group(function () {
        Route::get('/', [EtatBonCommandeController::class, 'index'])->name('etat_bons.index');
        Route::get('/show/{id}', [EtatBonCommandeController::class, 'show'])->name('etat_bons.show');

        // Export global sur une période
        Route::post('/export/pdf', [EtatBonCommandeController::class, 'exportPdf'])->name('etat_bons.exportPdf');
        Route::post('/export/excel', [EtatBonCommandeController::class, 'exportExcel'])->name('etat_bons.exportExcel');
// ✅ Export global (tous les bons filtrés)
        Route::get('/export/pdf', [EtatBonCommandeController::class, 'exportPdf'])->name('etat_bons.export_pdf');
        Route::get('/export/excel', [EtatBonCommandeController::class, 'exportExcel'])->name('etat_bons.export_excel');

        // Export d’un seul bon
        Route::get('/{id}/pdf', [EtatBonCommandeController::class, 'exportPdfOne'])->name('etat_bons.exportPdfOne');
        Route::get('/{id}/excel', [EtatBonCommandeController::class, 'exportExcelOne'])->name('etat_bons.exportExcelOne');

// Routes Etat Bons
        Route::prefix('etat-bons')->group(function () {
            // Liste principale avec filtres (dates, personnel, utilisateur)
            Route::get('/', [EtatBonCommandeController::class, 'index'])->name('etat_bons.index');

            // ✅ Export global (tous les bons filtrés)
            Route::get('/export/pdf', [EtatBonCommandeController::class, 'exportPdf'])->name('etat_bons.export_pdf');
            Route::get('/export/excel', [EtatBonCommandeController::class, 'exportExcel'])->name('etat_bons.export_excel');

            // ✅ Export individuel (un seul bon)
            Route::get('/{id}/export/pdf', [EtatBonCommandeController::class, 'exportPdfOne'])->name('etat_bons.pdf_one');
            Route::get('/{id}/export/excel', [EtatBonCommandeController::class, 'exportExcelOne'])->name('etat_bons.excel_one');

            // ✅ Détails d’un bon
            Route::get('/{id}', [EtatBonCommandeController::class, 'show'])->name('etat_bons.show');
        });

        // Détails d’un bon
        Route::get('/{id}', [EtatBonCommandeController::class, 'show'])->name('etat_bons.show');
    });
    Route::get('/budget/dashboard', function () {
        return view('Budget.index_budget')->with(['big_title' => 'Gestion des budgets']);
    })->name('budget');

    Route::prefix('budgets')->group(function () {
        // ✅ Liste principale avec filtres (dates)
        Route::get('/', [BudgetController::class, 'index'])->name('budgets.index');
      //  Route::get('budget', [BudgetController::class, 'index'])->name('budgets.index');

        // ✅ Création d’un budget
        Route::get('/create', [BudgetController::class, 'create'])->name('budgets.create');
        Route::post('/store', [BudgetController::class, 'store'])->name('budgets.store');

        // ✅ Modification d’un budget
        Route::get('/{id}/edit', [BudgetController::class, 'edit'])->name('budgets.edit');
      //  Route::post('/{id}/update', [BudgetController::class, 'update'])->name('budgets.update');
        Route::put('/{id}', [BudgetController::class, 'update'])->name('budgets.update'); // ✅ Correction ici

        // ✅ Suppression d’un budget
        Route::delete('/{id}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

        // ✅ Affichage des détails
        Route::get('/{id}', [BudgetController::class, 'show'])->name('budgets.show');

        // ✅ Export global (tous les budgets filtrés par période)
        Route::get('/export/pdf', [BudgetController::class, 'exportPdf'])->name('budgets.exportPdf');
        Route::get('/export/excel', [BudgetController::class, 'exportExcel'])->name('budgets.exportExcel');

        // ✅ Export individuel (un seul budget)
        Route::get('/{id}/export/pdf', [BudgetController::class, 'exportPdfOne'])->name('budgets.exportPdfOne');
        Route::get('/{id}/export/excel', [BudgetController::class, 'exportExcelOne'])->name('budgets.exportExcelOne');
        // ✅ Export global (tous les budgets filtrés par période)
        Route::get('/export/pdf/p', [BudgetController::class, 'exportPdf'])->name('budgets.export_pdf');
       Route::get('/export/excel/p', [BudgetController::class, 'exportExcel'])->name('budgets.export_excel');
//        // ✅ Export individuel (un seul budget)
       Route::get('/{id}/export/pdf/i', [BudgetController::class, 'exportPdfOne'])->name('budgets.export_pdf_one');
       Route::get('/{id}/export/excel/i', [BudgetController::class, 'exportExcelOne'])->name('budgets.export_excel_one');

    });

    Route::prefix('entrees-speciales')->name('entrees_speciales.')->group(function () {
        Route::get('/rappels', [EntreeSpecialeController::class, 'rappels'])->name('rappels');
        Route::get('/remboursements', [EntreeSpecialeController::class, 'remboursements'])->name('remboursements');
        Route::patch('/echeances/{id}/payer', [EntreeSpecialeController::class, 'payerEcheance'])->name('echeances.payer');
        Route::patch('/echeances/{id}/annuler-paiement', [EntreeSpecialeController::class, 'annulerPaiementEcheance'])->name('echeances.annuler_paiement');
        Route::get('/', [EntreeSpecialeController::class, 'index'])->name('index');
        Route::get('/create', [EntreeSpecialeController::class, 'create'])->name('create');
        Route::post('/', [EntreeSpecialeController::class, 'store'])->name('store');
        Route::get('/{id}', [EntreeSpecialeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EntreeSpecialeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EntreeSpecialeController::class, 'update'])->name('update');
        Route::delete('/{id}', [EntreeSpecialeController::class, 'destroy'])->name('destroy');
    });



    Route::prefix('ligne_budgetaire_entrees')->group(function () {
        // ✅ Liste principale
        Route::get('/', [LigneBudgetaireEntreeController::class, 'index'])->name('ligne_budgetaire_entrees.index');

        // ✅ Création d’une ligne
        Route::get('/create', [LigneBudgetaireEntreeController::class, 'create'])->name('ligne_budgetaire_entrees.create');
        Route::post('/store', [LigneBudgetaireEntreeController::class, 'store'])->name('ligne_budgetaire_entrees.store');

        // ✅ Modification d’une ligne
        Route::get('/{id}/edit', [LigneBudgetaireEntreeController::class, 'edit'])->name('ligne_budgetaire_entrees.edit');
        Route::put('/{id}', [LigneBudgetaireEntreeController::class, 'update'])->name('ligne_budgetaire_entrees.update');

        // ✅ Suppression
        Route::delete('/{id}', [LigneBudgetaireEntreeController::class, 'destroy'])->name('ligne_budgetaire_entrees.destroy');

        // ✅ Affichage des détails
        Route::get('/{id}', [LigneBudgetaireEntreeController::class, 'show'])->name('ligne_budgetaire_entrees.show');

        // ✅ Export global (toutes les lignes)
        Route::get('/export/pdf', [LigneBudgetaireEntreeController::class, 'exportPdf'])->name('ligne_budgetaire_entrees.exportPdf');
        Route::get('/export/excel', [LigneBudgetaireEntreeController::class, 'exportExcel'])->name('ligne_budgetaire_entrees.exportExcel');

        // ✅ Export individuel
        Route::get('/{id}/export/pdf', [LigneBudgetaireEntreeController::class, 'exportPdfOne'])->name('ligne_budgetaire_entrees.exportPdfOne');
        Route::get('/{id}/export/excel', [LigneBudgetaireEntreeController::class, 'exportExcelOne'])->name('ligne_budgetaire_entrees.exportExcelOne');

        // ✅ Alias alternatifs (comme dans budgets)
        Route::get('/export/pdf/p', [LigneBudgetaireEntreeController::class, 'exportPdf'])->name('ligne_budgetaire_entrees.export_pdf');
        Route::get('/export/excel/p', [LigneBudgetaireEntreeController::class, 'exportExcel'])->name('ligne_budgetaire_entrees.export_excel');

        Route::get('/{id}/export/pdf/i', [LigneBudgetaireEntreeController::class, 'exportPdfOne'])->name('ligne_budgetaire_entrees.export_pdf_one');
        Route::get('/{id}/export/excel/i', [LigneBudgetaireEntreeController::class, 'exportExcelOne'])->name('ligne_budgetaire_entrees.export_excel_one');
    });



    Route::prefix('ligne_budgetaire_sorties')->group(function () {
        Route::get('/', [LigneBudgetaireSortieController::class, 'index'])->name('ligne_budgetaire_sorties.index');
        Route::get('/create', [LigneBudgetaireSortieController::class, 'create'])->name('ligne_budgetaire_sorties.create');
        Route::post('/store', [LigneBudgetaireSortieController::class, 'store'])->name('ligne_budgetaire_sorties.store');
        Route::get('/{id}/edit', [LigneBudgetaireSortieController::class, 'edit'])->name('ligne_budgetaire_sorties.edit');
        Route::put('/{id}', [LigneBudgetaireSortieController::class, 'update'])->name('ligne_budgetaire_sorties.update');
        Route::delete('/{id}', [LigneBudgetaireSortieController::class, 'destroy'])->name('ligne_budgetaire_sorties.destroy');
        Route::get('/{id}', [LigneBudgetaireSortieController::class, 'show'])->name('ligne_budgetaire_sorties.show');

        // Exports
        Route::get('/export/pdf', [LigneBudgetaireSortieController::class, 'exportPdf'])->name('ligne_budgetaire_sorties.exportPdf');
        Route::get('/export/excel', [LigneBudgetaireSortieController::class, 'exportExcel'])->name('ligne_budgetaire_sorties.exportExcel');
        Route::get('/{id}/export/pdf', [LigneBudgetaireSortieController::class, 'exportPdfOne'])->name('ligne_budgetaire_sorties.exportPdfOne');
        Route::get('/{id}/export/excel', [LigneBudgetaireSortieController::class, 'exportExcelOne'])->name('ligne_budgetaire_sorties.exportExcelOne');
    });


    Route::prefix('element_ligne_budgetaire_sorties')->group(function () {
        Route::get('/{id_ligne}', [ElementLigneBudgetaireSortieController::class, 'index'])->name('element_sorties.index');
        Route::get('/{id_ligne}/create', [ElementLigneBudgetaireSortieController::class, 'create'])->name('element_sorties.create');
        Route::post('/{id_ligne}/generate', [ElementLigneBudgetaireSortieController::class, 'generateForm'])->name('element_sorties.generate');
        Route::post('/{id_ligne}/store', [ElementLigneBudgetaireSortieController::class, 'store'])->name('element_sorties.store');
    });

    // Gestion des éléments d’une ligne sortie
    Route::prefix('ligne_budgetaire_sorties/{ligne_id}/elements')->group(function () {
        Route::get('/manage', [ElementLigneBudgetaireSortieController::class, 'manage'])->name('element_sorties.manage');
        Route::get('/', [ElementLigneBudgetaireSortieController::class, 'indexElements'])->name('element_sorties.index');
        Route::get('/create', [ElementLigneBudgetaireSortieController::class, 'create'])->name('element_sorties.create');
        Route::post('/store', [ElementLigneBudgetaireSortieController::class, 'store'])->name('element_sorties.store');
    });

// CRUD élément direct
    Route::get('/element_sorties/{id}/edit', [ElementLigneBudgetaireSortieController::class, 'edit'])->name('element_sorties.edit');
    Route::put('/element_sorties/{id}', [ElementLigneBudgetaireSortieController::class, 'update'])->name('element_sorties.update');
    Route::delete('/element_sorties/{id}', [ElementLigneBudgetaireSortieController::class, 'destroy'])->name('element_sorties.destroy');


    Route::prefix('element_entrees')->group(function () {
        Route::get('/{ligne_id}/manage', [ElementLigneBudgetaireEntreeController::class, 'manage'])->name('element_entrees.manage');
        Route::get('/{ligne_id}/index', [ElementLigneBudgetaireEntreeController::class, 'indexElements'])->name('element_entrees.index');
        Route::get('/{ligne_id}/create', [ElementLigneBudgetaireEntreeController::class, 'create'])->name('element_entrees.create');
        Route::post('/{ligne_id}/store', [ElementLigneBudgetaireEntreeController::class, 'store'])->name('element_entrees.store');

        Route::get('/{id}/edit', [ElementLigneBudgetaireEntreeController::class, 'edit'])->name('element_entrees.edit');
        Route::put('/{id}/update', [ElementLigneBudgetaireEntreeController::class, 'update'])->name('element_entrees.update');

        Route::delete('/{id}/delete', [ElementLigneBudgetaireEntreeController::class, 'destroy'])->name('element_entrees.destroy');
    });
    Route::prefix('donnee_sorties')->middleware('auth')->group(function () {
        Route::get('/', [DonneeBudgetaireSortieController::class, 'index'])->name('donnee_sorties.index');
        Route::get('/create', [DonneeBudgetaireSortieController::class, 'create'])->name('donnee_sorties.create');
        Route::post('/store', [DonneeBudgetaireSortieController::class, 'store'])->name('donnee_sorties.store');
        Route::get('/{id}/edit', [DonneeBudgetaireSortieController::class, 'edit'])->name('donnee_sorties.edit');
        Route::put('/{id}', [DonneeBudgetaireSortieController::class, 'update'])->name('donnee_sorties.update');
        Route::delete('/{id}', [DonneeBudgetaireSortieController::class, 'destroy'])->name('donnee_sorties.destroy');

        // exports
     /*   Route::get('/export/excel', [DonneeBudgetaireSortieController::class, 'exportExcel'])->name('donnee_sorties.export.excel');
        Route::get('/export/pdf', [DonneeBudgetaireSortieController::class, 'exportPdf'])->name('donnee_sorties.export.pdf');*/

        Route::get('donnee_sorties/export/excel', [DonneeBudgetaireSortieController::class, 'exportExcel'])->name('donnee_sorties.export.excel');
        Route::get('donnee_sorties/export/pdf', [DonneeBudgetaireSortieController::class, 'exportPdf'])->name('donnee_sorties.export.pdf');
    });



    Route::middleware(['auth'])->group(function () {
        Route::get('donnee_entrees', [DonneeBudgetaireEntreeController::class, 'index'])->name('donnee_entrees.index');
        Route::get('donnee_entrees/create', [DonneeBudgetaireEntreeController::class, 'create'])->name('donnee_entrees.create');
        Route::post('donnee_entrees/store', [DonneeBudgetaireEntreeController::class, 'store'])->name('donnee_entrees.store');
        Route::get('donnee_entrees/{id}/edit', [DonneeBudgetaireEntreeController::class, 'edit'])->name('donnee_entrees.edit');
        Route::put('donnee_entrees/{id}', [DonneeBudgetaireEntreeController::class, 'update'])->name('donnee_entrees.update');
        Route::delete('donnee_entrees/{id}', [DonneeBudgetaireEntreeController::class, 'destroy'])->name('donnee_entrees.destroy');

        // Exports
        Route::get('donnee_entrees/export/excel', [DonneeBudgetaireEntreeController::class, 'exportExcel'])->name('donnee_entrees.export.excel');
        Route::get('donnee_entrees/export/pdf', [DonneeBudgetaireEntreeController::class, 'exportPdf'])->name('donnee_entrees.export.pdf');
    });

    Route::prefix('donnee_ligne_entrees')->middleware('auth')->group(function () {
        Route::get('/{donnee}/manage', [DonneeLigneBudgetaireEntreeController::class, 'manage'])->name('donnee_ligne_entrees.manage');
        Route::get('/{donnee}', [DonneeLigneBudgetaireEntreeController::class, 'index'])->name('donnee_ligne_entrees.index');
        Route::get('/{donnee}/create', [DonneeLigneBudgetaireEntreeController::class, 'create'])->name('donnee_ligne_entrees.create');
        Route::post('/{donnee}/store', [DonneeLigneBudgetaireEntreeController::class, 'store'])->name('donnee_ligne_entrees.store');
        Route::get('/edit/{id}', [DonneeLigneBudgetaireEntreeController::class, 'edit'])->name('donnee_ligne_entrees.edit');
        Route::put('/update/{id}', [DonneeLigneBudgetaireEntreeController::class, 'update'])->name('donnee_ligne_entrees.update');
        Route::delete('/destroy/{id}', [DonneeLigneBudgetaireEntreeController::class, 'destroy'])->name('donnee_ligne_entrees.destroy');
        // routes/web.php
       /* Route::get('/get-elements-by-donnee/{donnee_id}', [\App\Http\Controllers\Budget\DonneeLigneBudgetaireEntreeController::class, 'getElements'])
            ->name('donnee_ligne_entrees.getElements');*/

// web.php
        // Liste des éléments rattachés à une donnée budgétaire d’entrée
        Route::get('/get-elements-by-donnee/{donnee_id}',
            [\App\Http\Controllers\Budget\DonneeLigneBudgetaireEntreeController::class, 'getElements']
        )->name('donnee_ligne_entrees.getElements');
    /*    Route::get('/get-elements-by-donnee/{donnee_id}',
            [\App\Http\Controllers\Budget\DonneeLigneBudgetaireEntreeController::class, 'getElements']
        )->name('donnee_ligne_entrees.elements');*/
        // ✅ Export
        Route::get('/{donnee}/export/pdf', [DonneeLigneBudgetaireEntreeController::class, 'exportPdf'])->name('donnee_ligne_entrees.export.pdf');
        Route::get('/{donnee}/export/excel', [DonneeLigneBudgetaireEntreeController::class, 'exportExcel'])->name('donnee_ligne_entrees.export.excel');
    });


    Route::prefix('donnee_ligne_sorties')->name('donnee_ligne_sorties.')->group(function () {
        Route::get('/{donnee}/manage', [DonneeLigneBudgetaireSortieController::class, 'manage'])->name('manage');
        Route::get('/{donnee}', [DonneeLigneBudgetaireSortieController::class, 'index'])->name('index');
        Route::get('/{donnee}/create', [DonneeLigneBudgetaireSortieController::class, 'create'])->name('create');
        Route::post('/{donnee}', [DonneeLigneBudgetaireSortieController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [DonneeLigneBudgetaireSortieController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [DonneeLigneBudgetaireSortieController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [DonneeLigneBudgetaireSortieController::class, 'destroy'])->name('destroy');
        Route::get('/{donnee}/export/pdf', [DonneeLigneBudgetaireSortieController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{donnee}/export/excel', [DonneeLigneBudgetaireSortieController::class, 'exportExcel'])->name('export.excel');

        Route::get('/donnee_ligne_sorties/{donnee}/get-elements', [DonneeLigneBudgetaireSortieController::class, 'getElements'])
            ->name('getElements');

    });

    // 📌 Liste des cycles
    Route::get('cycles', [\App\Http\Controllers\Admin\CycleController::class, 'index'])
        ->name('cycle_management');

// 📌 Ajouter un cycle
    Route::post('cycles/store', [\App\Http\Controllers\Admin\CycleController::class, 'store'])
        ->name('store_cycle');

// 📌 Modifier un cycle
    Route::post('cycles/update', [\App\Http\Controllers\Admin\CycleController::class, 'update'])
        ->name('update_cycle');

// 📌 Supprimer un cycle
    Route::delete('cycles/{id}', [\App\Http\Controllers\Admin\CycleController::class, 'destroy'])
        ->name('delete_cycle');

// 📌 Liste des filières
    Route::get('filieres', [\App\Http\Controllers\Admin\FiliereController::class, 'index'])
        ->name('filiere_management');

// 📌 Ajouter une filière
    Route::post('filieres/store', [\App\Http\Controllers\Admin\FiliereController::class, 'store'])
        ->name('store_filiere');

// 📌 Modifier une filière
    Route::post('filieres/update', [\App\Http\Controllers\Admin\FiliereController::class, 'update'])
        ->name('update_filiere');

// 📌 Supprimer une filière
    Route::delete('filieres/{id}', [\App\Http\Controllers\Admin\FiliereController::class, 'destroy'])
        ->name('delete_filiere');

// 📌 Liste des spécialités
    Route::get('specialites', [\App\Http\Controllers\Admin\SpecialiteController::class, 'index'])
        ->name('specialite_management');

// 📌 Ajouter une spécialité
    Route::post('specialites/store', [\App\Http\Controllers\Admin\SpecialiteController::class, 'store'])
        ->name('store_specialite');

// 📌 Modifier une spécialité
    Route::post('specialites/update', [\App\Http\Controllers\Admin\SpecialiteController::class, 'update'])
        ->name('update_specialite');

// 📌 Supprimer une spécialité
    Route::delete('specialites/{id}', [\App\Http\Controllers\Admin\SpecialiteController::class, 'destroy'])
        ->name('delete_specialite');
// 📌 Liste des niveaux
    Route::get('niveaux', [\App\Http\Controllers\Admin\NiveauController::class, 'index'])
        ->name('niveau_management');

// 📌 Ajouter un niveau
    Route::post('niveaux/store', [\App\Http\Controllers\Admin\NiveauController::class, 'store'])
        ->name('store_niveau');

// 📌 Modifier un niveau
    Route::post('niveaux/update', [\App\Http\Controllers\Admin\NiveauController::class, 'update'])
        ->name('update_niveau');

// 📌 Supprimer un niveau
    Route::delete('niveaux/{id}', [\App\Http\Controllers\Admin\NiveauController::class, 'destroy'])
        ->name('delete_niveau');

    // 📌 Liste des scolarités
    Route::get('scolarites', [\App\Http\Controllers\Admin\ScolariteController::class, 'index'])
        ->name('scolarite_management');

// 📌 Ajouter une scolarité
    Route::post('scolarites/store', [\App\Http\Controllers\Admin\ScolariteController::class, 'store'])
        ->name('store_scolarite');

// 📌 Modifier une scolarité
    Route::post('scolarites/update', [\App\Http\Controllers\Admin\ScolariteController::class, 'update'])
        ->name('update_scolarite');

// 📌 Supprimer une scolarité
    Route::delete('scolarites/{id}', [\App\Http\Controllers\Admin\ScolariteController::class, 'destroy'])
        ->name('delete_scolarite');

// 📌 Gestion des tranches de scolarité
   /* Route::get('scolarites/{id}/tranches', [TrancheScolariteController::class, 'manage'])
        ->name('tranche_scolarite.manage');
    Route::get('scolarites/{id}/tranches', [TrancheScolariteController::class, 'manage'])
        ->name('tranche_scolarite.manage');*/
    // 📌 Liste des tranches d’une scolarité

    Route::get('scolarites/{id}/tranches', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'manage'])
        ->name('tranche_scolarite_manage');

    Route::get('scolarites2/{id}/tranches', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'index'])
        ->name('tranche_scolarite.index');
    Route::get('scolarites1/{id}/tranches/create', [TrancheScolariteController::class, 'create'])
        ->name('tranche_scolarite.create');

    Route::post('scolarites/{id}/tranches/store', [TrancheScolariteController::class, 'store'])
        ->name('tranche_scolarite.store');

    Route::get('scolarites/{id}/tranches/edit', [TrancheScolariteController::class, 'editForm'])
        ->name('tranche_scolarite.editForm');

    Route::post('scolarites/{id}/tranches/updateAll', [TrancheScolariteController::class, 'updateAll'])
        ->name('tranche_scolarite.updateAll');

    Route::delete('tranches/{id}', [TrancheScolariteController::class, 'destroy'])
        ->name('tranche_scolarite.delete');
    // 📌 Mettre à jour une tranche
    Route::put('tranches/{id}', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'update'])
        ->name('tranche_scolarite.update');

// 📌 Supprimer une tranche
    Route::delete('tranches/{id}', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'destroy'])
        ->name('tranche_scolarite.destroy');
    Route::get('scolarites/{id}/tranches/export-pdf', [TrancheScolariteController::class, 'exportPdf'])
        ->name('tranche_scolarite.exportPdf');
// 📌 Liste des caisses
    Route::get('caisses', [\App\Http\Controllers\Admin\CaisseController::class, 'index'])
        ->name('caisse_management');

// 📌 Ajouter une caisse
    Route::post('caisses/store', [\App\Http\Controllers\Admin\CaisseController::class, 'store'])
        ->name('store_caisse');

// 📌 Modifier une caisse
    Route::post('caisses/update', [\App\Http\Controllers\Admin\CaisseController::class, 'update'])
        ->name('update_caisse');

// 📌 Supprimer une caisse
    Route::delete('caisses/{id}', [\App\Http\Controllers\Admin\CaisseController::class, 'destroy'])
        ->name('delete_caisse');
    Route::get('caisses-affectations', [\App\Http\Controllers\Admin\CaisseController::class, 'affectations'])
        ->name('caisses.affectations');
    Route::post('caisses-affectations', [\App\Http\Controllers\Admin\CaisseController::class, 'storeAffectation'])
        ->name('caisses.affectations.store');
    Route::delete('caisses-affectations/{id}', [\App\Http\Controllers\Admin\CaisseController::class, 'destroyAffectation'])
        ->name('caisses.affectations.destroy');

    // 📌 Liste des banques
    Route::get('banques', [\App\Http\Controllers\Admin\BanqueController::class, 'index'])
        ->name('banque_management');

// 📌 Ajouter une banque
    Route::post('banques/store', [\App\Http\Controllers\Admin\BanqueController::class, 'store'])
        ->name('store_banque');

// 📌 Modifier une banque
    Route::post('banques/update', [\App\Http\Controllers\Admin\BanqueController::class, 'update'])
        ->name('update_banque');

// 📌 Supprimer une banque
    Route::delete('banques/{id}', [\App\Http\Controllers\Admin\BanqueController::class, 'destroy'])
        ->name('delete_banque');
    Route::get('banques-affectations', [\App\Http\Controllers\Admin\BanqueController::class, 'affectations'])
        ->name('banques.affectations');
    Route::post('banques-affectations', [\App\Http\Controllers\Admin\BanqueController::class, 'storeAffectation'])
        ->name('banques.affectations.store');
    Route::delete('banques-affectations/{id}', [\App\Http\Controllers\Admin\BanqueController::class, 'destroyAffectation'])
        ->name('banques.affectations.destroy');

    // 📌 Liste des entités
    Route::get('entites', [\App\Http\Controllers\Admin\EntiteController::class, 'index'])
        ->name('entite_management');

// 📌 Ajouter une entité
    Route::post('entites/store', [\App\Http\Controllers\Admin\EntiteController::class, 'store'])
        ->name('store_entite');

// 📌 Modifier une entité
    Route::post('entites/update', [\App\Http\Controllers\Admin\EntiteController::class, 'update'])
        ->name('update_entite');


    // 📌 Liste des transferts
    Route::get('transferts', [\App\Http\Controllers\Admin\TransfertCaisseController::class, 'index'])
        ->name('transfert_management');

// 📌 Ajouter un transfert
    Route::post('transferts/store', [\App\Http\Controllers\Admin\TransfertCaisseController::class, 'store'])
        ->name('store_transfert');

// 📌 Modifier un transfert
    Route::post('transferts/update', [\App\Http\Controllers\Admin\TransfertCaisseController::class, 'update'])
        ->name('update_transfert');

// 📌 Supprimer un transfert
    Route::delete('transferts/{id}', [\App\Http\Controllers\Admin\TransfertCaisseController::class, 'destroy'])
        ->name('delete_transfert');
// 📌 Supprimer une entité
    Route::delete('entites/{id}', [\App\Http\Controllers\Admin\EntiteController::class, 'destroy'])
        ->name('delete_entite');

    Route::get('/etudiants/dashboard', function () {
        return view('Admin.Etudiant.index_etudiant')->with(['title' => 'Gestion des étudiants']);
    })->name('etudiant');
// Étudiantsdonnee_entrees/create
    Route::get('etudiants', [\App\Http\Controllers\Admin\EtudiantController::class, 'index'])
        ->name('etudiant_management');
    Route::get('etudiants/import', [\App\Http\Controllers\Admin\EtudiantController::class, 'import'])
        ->name('etudiants.import');
    Route::get('etudiants/import/template', [\App\Http\Controllers\Admin\EtudiantController::class, 'downloadTemplate'])
        ->name('etudiants.import.template');
    Route::post('etudiants/import', [\App\Http\Controllers\Admin\EtudiantController::class, 'importStore'])
        ->name('etudiants.import.store');
    Route::post('etudiants/store', [\App\Http\Controllers\Admin\EtudiantController::class, 'store'])
        ->name('store_etudiant');
    Route::post('etudiants/update', [\App\Http\Controllers\Admin\EtudiantController::class, 'update'])
        ->name('update_etudiant');
    Route::delete('etudiants/{id}', [\App\Http\Controllers\Admin\EtudiantController::class, 'destroy'])
        ->name('delete_etudiant');
    // 📌 Frais
    Route::get('frais', [\App\Http\Controllers\Admin\FraisController::class, 'index'])
        ->name('frais_management');

    Route::post('frais/store', [\App\Http\Controllers\Admin\FraisController::class, 'store'])
        ->name('store_frais');

    Route::post('frais/update', [\App\Http\Controllers\Admin\FraisController::class, 'update'])
        ->name('update_frais');

    Route::delete('frais/{id}', [\App\Http\Controllers\Admin\FraisController::class, 'destroy'])
        ->name('delete_frais');
// 📌 Personnel


    Route::get('/personnel/dashboard', function () {
        return view('Admin.Personnel.index_personnel')->with(['title' => 'Gestion du personnel']);
    })->name('personnel');

    Route::get('personnels', [\App\Http\Controllers\Admin\PersonnelController::class, 'index'])
        ->name('personnel_management');

    Route::get('personnels/import', [\App\Http\Controllers\Admin\PersonnelController::class, 'import'])
        ->name('personnels.import');

    Route::get('personnels/import/template', [\App\Http\Controllers\Admin\PersonnelController::class, 'downloadTemplate'])
        ->name('personnels.import.template');

    Route::post('personnels/import', [\App\Http\Controllers\Admin\PersonnelController::class, 'importStore'])
        ->name('personnels.import.store');

    Route::post('personnels/store', [\App\Http\Controllers\Admin\PersonnelController::class, 'store'])
        ->name('store_personnel');

    Route::post('personnels/update', [\App\Http\Controllers\Admin\PersonnelController::class, 'update'])
        ->name('update_personnel');

    Route::delete('personnels/{id}', [\App\Http\Controllers\Admin\PersonnelController::class, 'destroy'])
        ->name('delete_personnel');
// 📌 Fonctions
    Route::get('fonctions', [\App\Http\Controllers\Admin\FonctionController::class, 'index'])
        ->name('fonction_management');

    Route::post('fonctions/store', [\App\Http\Controllers\Admin\FonctionController::class, 'store'])
        ->name('store_fonction');

    Route::post('fonctions/update', [\App\Http\Controllers\Admin\FonctionController::class, 'update'])
        ->name('update_fonction');

    Route::delete('fonctions/{id}', [\App\Http\Controllers\Admin\FonctionController::class, 'destroy'])
        ->name('delete_fonction');

// 📌 Années académiques
    Route::get('annees-academiques', [\App\Http\Controllers\Admin\AnneeAcademiqueController::class, 'index'])
        ->name('annee_academique_management');

    Route::post('annees-academiques/store', [\App\Http\Controllers\Admin\AnneeAcademiqueController::class, 'store'])
        ->name('store_annee_academique');

    Route::post('annees-academiques/update', [\App\Http\Controllers\Admin\AnneeAcademiqueController::class, 'update'])
        ->name('update_annee_academique');

    Route::delete('annees-academiques/{id}', [\App\Http\Controllers\Admin\AnneeAcademiqueController::class, 'destroy'])
        ->name('delete_annee_academique');

    // ===== Factures étudiant =====
    Route::get('etudiants/{id}/factures', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'indexByEtudiant'])
        ->name('factures_by_etudiant');

    Route::post('factures/store', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'store'])
        ->name('store_facture');

    Route::post('factures/update', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'update'])
        ->name('update_facture');

    Route::delete('factures/{id}', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'destroy'])
        ->name('delete_facture');

// Liste des étudiants ayant au moins une facture
    Route::get('etudiants-avec-factures', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'etudiantsAvecFactures'])
        ->name('etudiants_avec_factures');

// ===== AJAX dépendances depuis scolarites =====
    Route::get('ajax/scolarite/filters', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'filtersFromCycleFiliere'])
        ->name('ajax_scolarite_filters'); // ?id_cycle=&id_filiere=

    Route::get('ajax/scolarite/{id}/tranches', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'tranchesByScolarite'])
        ->name('ajax_tranches_by_scolarite');

// ===== Tranches scolarité (CRUD simple) =====
    Route::get('tranches', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'index'])
        ->name('tranche_management');

    Route::post('tranches/store', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'store'])
        ->name('store_tranche');

    Route::post('tranches/update', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'update'])
        ->name('update_tranche');

    Route::delete('tranches/{id}', [\App\Http\Controllers\Admin\TrancheScolariteController::class, 'destroy'])
        ->name('delete_tranche');

// AJAX budgets (cascade)
    Route::get('ajax/budget/{budget}/lignes', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'ajaxLignesByBudget'])
        ->name('ajax_lignes_by_budget');
    Route::get('ajax/ligne/{ligne}/elements', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'ajaxElementsByLigne'])
        ->name('ajax_elements_by_ligne');
    Route::get('ajax/element/{element}/donnees', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'ajaxDonneesByElement'])
        ->name('ajax_donnees_by_element');
// PDF
    Route::get('factures/{id}/pdf', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'showPdf'])
        ->name('facture_pdf');
    Route::get('factures/{id}/download', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'downloadPdf'])
        ->name('facture_download');

/*// Impression / PDF
    Route::get('factures/{id}/pdf', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'showPdf'])
        ->name('facture_pdf'); // HTML imprimable (2 souches A4)
    Route::get('factures/{id}/pdf/download', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'downloadPdf'])
        ->name('facture_pdf_download'); // nécessite laravel-dompdf*/


    // --- Nouveaux pour la double-dépendance ---
    Route::get('ajax/element/{element}/donnees-budgetaires', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'ajaxDonneesBudgetairesByElement'])
        ->name('ajax_donnees_budgetaires_by_element'); // renvoie donnee_budgetaire_entrees

    Route::get('ajax/element/{element}/donnees-ligne', [\App\Http\Controllers\Admin\FactureEtudiantController::class, 'ajaxDonneesLigneByElementAndDonneeBudgetaire'])
        ->name('ajax_donnees_ligne_by_element'); // param ?id_budget= & ?id_donnee_budgetaire=


    // routes/web.php


   /* Route::prefix('reglements')->middleware(['auth'])->group(function () {
        // Listing + création depuis une facture
        Route::get('/etudiant/{etudiant}', [ReglementEtudiantController::class, 'indexByEtudiant'])
            ->name('reglement_by_etudiant');

        Route::get('/facture/{facture}/create', [ReglementEtudiantController::class, 'createFromFacture'])
            ->name('reglement_from_facture');

        Route::post('/store', [ReglementEtudiantController::class, 'store'])
            ->name('store_reglement');

        Route::post('/update', [ReglementEtudiantController::class, 'update'])
            ->name('update_reglement');

        Route::delete('/{id}', [ReglementEtudiantController::class, 'destroy'])
            ->name('delete_reglement');

        // PDF
        Route::get('/{id}/pdf', [ReglementEtudiantController::class, 'showPdf'])
            ->name('reglement_pdf');
        Route::get('/{id}/download', [ReglementEtudiantController::class, 'downloadPdf'])
            ->name('reglement_download_pdf');

        // AJAX Scolarité
        Route::get('/ajax/filters', [ReglementEtudiantController::class, 'filtersFromCycleFiliere'])
            ->name('ajax_regl_filters'); // id_cycle, id_filiere => niveaux, specialites, scolarites
        Route::get('/ajax/scolarite/{id}/tranches', [ReglementEtudiantController::class, 'tranchesByScolarite'])
            ->name('ajax_regl_tranches');

        // AJAX Budget
        Route::get('/ajax/budget/{budget}/lignes', [ReglementEtudiantController::class, 'ajaxLignesByBudget'])
            ->name('ajax_regl_lignes');
        Route::get('/ajax/ligne/{ligne}/elements', [ReglementEtudiantController::class, 'ajaxElementsByLigne'])
            ->name('ajax_regl_elements');
        Route::get('/ajax/element/{element}/donnees', [ReglementEtudiantController::class, 'ajaxDonneesByElement'])
            ->name('ajax_regl_donnees');
    });*/


    Route::prefix('reglements')->middleware(['auth'])->group(function () {
        // Tableau des règlements d’une facture
        Route::get('/facture/{facture}', [ReglementEtudiantController::class, 'indexByFacture'])
            ->name('reglement_by_facture');

        // routes/web.php (dans le groupe prefix('reglements'))
        Route::get('/{id}/edit', [ReglementEtudiantController::class, 'edit'])
            ->name('edit_reglement');

        // Formulaire de création depuis une facture
        Route::get('/facture/{facture}/create', [ReglementEtudiantController::class, 'createFromFacture'])
            ->name('reglement_from_facture');
        Route::get(
            '/ajax/element/{element}/donnees-by-element',
            [\App\Http\Controllers\Admin\ReglementEtudiantController::class, 'ajaxDonneesLigneByElement']
        )->name('ajax_regl_donnees_by_element');

        // 🔹 Filtres pédagogiques (cycle+filiere => niveaux, specialites, scolarites)
        Route::get('/ajax/filters', [\App\Http\Controllers\Admin\ReglementEtudiantController::class, 'filtersFromCycleFiliere'])
            ->name('ajax_regl_filters');
        // CRUD
        Route::post('/store', [ReglementEtudiantController::class, 'store'])->name('store_reglement');
        Route::post('/update', [ReglementEtudiantController::class, 'update'])->name('update_reglement');
        Route::delete('/{id}', [ReglementEtudiantController::class, 'destroy'])->name('delete_reglement');

        // PDF
        Route::get('/{id}/pdf', [ReglementEtudiantController::class, 'showPdf'])->name('reglement_pdf');
        Route::get('/{id}/download', [ReglementEtudiantController::class, 'downloadPdf'])->name('reglement_download_pdf');

        // AJAX Budget cascade
        Route::get('/ajax/budget/{budget}/lignes', [ReglementEtudiantController::class, 'ajaxLignesByBudget'])->name('ajax_regl_lignes');
        Route::get('/ajax/ligne/{ligne}/elements', [ReglementEtudiantController::class, 'ajaxElementsByLigne'])->name('ajax_regl_elements');
        Route::get('/ajax/ligne/{ligne}/donnees-budget', [ReglementEtudiantController::class, 'ajaxDonneesBudgetByLigne'])->name('ajax_regl_donnees_budget');
        Route::get('/ajax/element/{element}/donnees-ligne', [ReglementEtudiantController::class, 'ajaxDonneesLigneByElement'])->name('ajax_regl_donnees_ligne');

        // (Optionnel) AJAX scolarité → tranches
        Route::get('/ajax/scolarite/{id}/tranches', [ReglementEtudiantController::class, 'tranchesByScolarite'])->name('ajax_regl_tranches');
    });


    Route::prefix('reporting')->middleware(['auth'])->group(function () {
        Route::get('/factures', [ReportingController::class, 'facturesAvecReglements']);
        Route::get('/budget/atterrissage', [ReportingController::class, 'atterrissageBudgetaire']);

        Route::get('/factures/export/excel', [ReportingController::class, 'exportFacturesExcel']);
        Route::get('/factures/export/pdf', [ReportingController::class, 'exportFacturesPdf']);
    });

  /*  Route::prefix('etats')->middleware(['auth'])->group(function () {

        // 🟦 HUB des états
        Route::get('/', [EtatComptableController::class, 'index'])
            ->name('etats.index');

        // 🟩 Pilotage budgétaire (index_budget)
        Route::get('/budget', [EtatComptableController::class, 'indexBudget'])
            ->name('etat_budget');

        // 🟨 Atterrissage budgétaire (résultats + calculs)
        Route::get('/atterrissage-budgetaire', [EtatComptableController::class, 'atterrissageBudgetaire'])
            ->name('etat_atterrissage_budgetaire');

        // 🧾 Factures & règlements
        Route::get('/factures-reglements', [EtatComptableController::class, 'facturesReglements'])
            ->name('etat_factures_reglements');

        // 🎓 Situation étudiant
        Route::get('/situation-etudiant', [EtatComptableController::class, 'situationEtudiant'])
            ->name('etat_situation_etudiant');
    });*/
// routes/web.php

    Route::prefix('decaissements')->name('decaissements.')->group(function () {

        Route::get('/index-decaissement', [DecaissementController::class, 'index'])->name('index');

        Route::get('/financer/{id}', [DecaissementController::class, 'create'])->name('create');

        Route::post('/store', [DecaissementController::class, 'store'])->name('store');

        Route::delete('/delete/{id}', [DecaissementController::class, 'destroy'])->name('destroy');

        Route::get('/reporting', [DecaissementController::class, 'reporting'])->name('reporting');
        Route::get('/etat-bons-realises', [DecaissementController::class, 'etatRealises'])->name('etat_realises');
        Route::get('/etat-bons-realises/pdf', [DecaissementController::class, 'etatRealisesPdf'])->name('etat_realises.pdf');
        Route::get('/etat-bons-non-finances', [DecaissementController::class, 'etatNonFinances'])->name('etat_non_finances');
        Route::get('/etat-bons-non-finances/pdf', [DecaissementController::class, 'etatNonFinancesPdf'])->name('etat_non_finances.pdf');

        Route::get('/pdf', [DecaissementController::class, 'exportPdf'])->name('pdf');
        // web.php
        Route::get('/decaissements/bon/{id}', [DecaissementController::class, 'detailBon'])
            ->name('detailBon');
        Route::get('/recu/{id}', [DecaissementController::class, 'recu'])
            ->name('recu');
        Route::delete('/bon/{bon}/decaissement/{decaissement}', [DecaissementController::class, 'destroyDecaissement'])
            ->name('destroy_decaissement');
        Route::get('/ajax/lignes/{budget}', [DecaissementController::class, 'getLignes']);
        Route::get('/ajax/elements/{ligne}', [DecaissementController::class, 'getElements']);
        Route::get('/ajax/donnees-budget/{ligne}', [DecaissementController::class, 'getDonneesBudget']);
        Route::get('/ajax/donnees-ligne/{element}', [DecaissementController::class, 'getDonneesLigne']);
        Route::get('/ajax/solde-caisse/{id}', [DecaissementController::class, 'getSoldeAjax']);
        Route::get('/ajax/transfert-caisse/{id}', [DecaissementController::class, 'getTransfertCaisse']);


    });
    Route::prefix('etats')->middleware(['auth'])->group(function () {

        // 🟦 HUB des états
        Route::get('/', [EtatComptableController::class, 'index'])
            ->name('etats.index');

        // 🟩 Pilotage budgétaire
        Route::get('/budget', [EtatComptableController::class, 'indexBudget'])
            ->name('etat_budget');

        // 🟨 Atterrissage budgétaire
        Route::get('/atterrissage-budgetaire', [EtatComptableController::class, 'atterrissageBudgetaire'])
            ->name('etat_atterrissage_budgetaire');

        // 📤 EXPORTS (⬅️ MANQUAIENT ICI)
        Route::get('/atterrissage-budgetaire/export/excel', [EtatComptableController::class, 'exportEtatBudgetaireExcel'])
            ->name('etat_budget_export_excel');

        Route::get('/atterrissage-budgetaire/export/pdf', [EtatComptableController::class, 'exportEtatBudgetairePdf'])
            ->name('etat_budget_export_pdf');

        // 🧾 Factures & règlements
        Route::get('/factures-reglements', [EtatComptableController::class, 'facturesReglements'])
            ->name('etat_factures_reglements');
        Route::get('/factures-reglements/export/excel',
            [EtatComptableController::class, 'exportFacturesReglementsExcel']
        )->name('etat_factures_export_excel');


        // 🔴 Export PDF
      /*   Route::get('/factures-reglements/export/pdf',
             [EtatComptableController::class, 'exportEtatBudgetairePdf']
         )->name('etat_factures_export_pdf');*/
       Route::get('/factures-reglements/export/pdf',
            [EtatComptableController::class, 'exportFacturesReglementsPdf']
        )->name('etat_factures_export_pdf');
        // 🎓 Situation étudiant
        Route::get('/situation-etudiant', [EtatComptableController::class, 'situationEtudiant'])
            ->name('etat_situation_etudiant');

        // 🎓 Situation étudiant – Export PDF
        Route::get('/situation-etudiant/export/pdf',
            [EtatComptableController::class, 'exportSituationEtudiantPdf']
        )->name('etat_situation_etudiant_pdf');

    });

    Route::prefix('etat-sorties')->name('etat_sorties.')->group(function () {

        // 🔹 INDEX
        Route::get('/', [EtatSortieController::class, 'index'])->name('index');

        // 🔹 PILOTAGE
        Route::get('/pilotage', [EtatSortieController::class, 'pilotage'])->name('pilotage');

        // 🔹 ATTERRISSAGE
        Route::get('/atterrissage', [EtatSortieController::class, 'atterrissage'])->name('atterrissage');

        // 🔹 DÉCAISSEMENTS
        Route::get('/decaissements', [EtatSortieController::class, 'decaissements'])->name('decaissements');
        Route::get('/etat-caisse', [EtatSortieController::class, 'etatCaisse'])->name('etat_caisse');
        Route::get('/etat-caisse/pdf', [EtatSortieController::class, 'exportEtatCaissePdf'])->name('etat_caisse.pdf');
        Route::get('/etat-caisse/excel', [EtatSortieController::class, 'exportEtatCaisseExcel'])->name('etat_caisse.excel');
        Route::get('/mon-etat-caisse', [EtatSortieController::class, 'monEtatCaisse'])->name('mon_etat_caisse');
        Route::get('/mon-etat-caisse/pdf', [EtatSortieController::class, 'exportMonEtatCaissePdf'])->name('mon_etat_caisse.pdf');
        Route::get('/mon-etat-caisse/excel', [EtatSortieController::class, 'exportMonEtatCaisseExcel'])->name('mon_etat_caisse.excel');
        Route::get('/disponibilite-caisses', [EtatSortieController::class, 'disponibiliteCaisses'])->name('disponibilite_caisses');
        Route::get('/disponibilite-caisses/pdf', [EtatSortieController::class, 'exportDisponibiliteCaissesPdf'])->name('disponibilite_caisses.pdf');

        // 🔹 BON
        Route::get('/bon/{id}', [EtatSortieController::class, 'bon'])->name('bon');

        // 🔹 EXPORT
        Route::get('/atterrissage/pdf', [EtatSortieController::class, 'exportPdf'])->name('pdf');
        Route::get('/atterrissage/excel', [EtatSortieController::class, 'exportExcel'])->name('excel');
        Route::get('/global', [EtatSortieController::class, 'etatGlobal'])
            ->name('global');

        Route::get('/global/pdf', [EtatSortieController::class, 'exportGlobalPdf'])
            ->name('global.pdf');

        Route::get('/global/excel', [EtatSortieController::class, 'exportGlobalExcel'])
            ->name('global.excel');


    });

    Route::prefix('retour-caisses')->name('retour_caisses.')->group(function () {
        Route::get('/', [RetourCaisseController::class, 'index'])->name('index');
        Route::get('/create', [RetourCaisseController::class, 'create'])->name('create');
        Route::post('/', [RetourCaisseController::class, 'store'])->name('store');
        Route::get('/mes-retours', [RetourCaisseController::class, 'mine'])->name('mine');
        Route::get('/export/pdf', [RetourCaisseController::class, 'exportPdf'])->name('pdf');
        Route::get('/mes-retours/export/pdf', [RetourCaisseController::class, 'exportMinePdf'])->name('mine.pdf');
       Route::get('/decaissements/{bon}', [RetourCaisseController::class, 'getDecaissements'])
    ->name('decaissements');
    Route::delete('/{retour}', [RetourCaisseController::class, 'destroy'])
    ->name('destroy');
Route::get('/decaissement-details/{decaissement}', [RetourCaisseController::class, 'getDecaissementDetails'])
    ->name('decaissement_details');
    });
/* 
http://localhost/GestFinance/public/recalcul-montants-entrees
Route::get('/recalcul-montants-entrees', function () {
    DB::transaction(function () {
        $lignes = facture_etudiant::select(
                'id_donnee_ligne_budgetaire_entree',
                DB::raw('SUM(montant_total_facture) as total')
            )
            ->whereNotNull('id_donnee_ligne_budgetaire_entree')
            ->groupBy('id_donnee_ligne_budgetaire_entree')
            ->get();

        donnee_ligne_budgetaire_entree::query()->update(['montant' => 0]);

        foreach ($lignes as $ligne) {
            donnee_ligne_budgetaire_entree::where('id', $ligne->id_donnee_ligne_budgetaire_entree)
                ->update(['montant' => $ligne->total]);
        }

        $parents = facture_etudiant::select(
                'id_donnee_budgetaire_entree',
                DB::raw('SUM(montant_total_facture) as total')
            )
            ->whereNotNull('id_donnee_budgetaire_entree')
            ->groupBy('id_donnee_budgetaire_entree')
            ->get();

        donnee_budgetaire_entree::query()->update(['montant' => 0]);

        foreach ($parents as $parent) {
            donnee_budgetaire_entree::where('id', $parent->id_donnee_budgetaire_entree)
                ->update(['montant' => $parent->total]);
        }
    });

    return 'Montants prévisionnels des entrées recalculés avec succès ✅';
}); */
});
