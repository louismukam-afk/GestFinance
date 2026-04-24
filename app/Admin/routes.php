<?php

use Illuminate\Routing\Router;
use App\Admin\Controllers\Bon_commandeokController;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('annee_academiques', \App\Admin\Controllers\anneeacademiqueController::class);
    $router->resource('banques', \App\Admin\Controllers\BanqueController::class);
    $router->resource('bon_commandeoks', \App\Admin\Controllers\Bon_commandeokController::class);
    $router->resource('budgets', \App\Admin\Controllers\BudgetController::class);
    $router->resource('caisses', \App\Admin\Controllers\CaisseController::class);
    $router->resource('cycles', \App\Admin\Controllers\CycleController::class);
    $router->resource('decaissements', \App\Admin\Controllers\DecaissementController::class);
    $router->resource('donnee_budgetaire_entrees', \App\Admin\Controllers\Donnee_budgetaire_entreeController::class);
    $router->resource('donnee_budgetaire_sorties', \App\Admin\Controllers\Donnee_budgetaire_sortieController::class);
    $router->resource('donnee_ligne_budgetaire_entrees', \App\Admin\Controllers\Donnee_ligne_budgetaire_entreeController::class);
    $router->resource('donnee_ligne_budgetaire_sorties', \App\Admin\Controllers\Donnee_ligne_budgetaire_sortieController::class);
    $router->resource('element_bon_commandes', \App\Admin\Controllers\Element_bon_commandeController::class);
    $router->resource('element_ligne_budgetaire_entrees', \App\Admin\Controllers\Element_ligne_budgetaire_entreeController::class);
    $router->resource('element_ligne_budgetaire_sorties', \App\Admin\Controllers\Element_ligne_budgetaire_sortieController::class);
    $router->resource('entites', \App\Admin\Controllers\EntiteController::class);
    $router->resource('etudiants', \App\Admin\Controllers\EtudiantController::class);
    $router->resource('facture_etudiants', \App\Admin\Controllers\Facture_etudiantController::class);
    $router->resource('filieres', \App\Admin\Controllers\FiliereController::class);
    $router->resource('fonctions', \App\Admin\Controllers\FonctionController::class);
    $router->resource('frais', \App\Admin\Controllers\FraisController::class);
    $router->resource('ligne_budgetaire_-entrees', \App\Admin\Controllers\Ligne_budgetaire_EntreeController::class);
    $router->resource('ligne_budgetaire_sorties', \App\Admin\Controllers\Ligne_budgetaire_sortieController::class);
    $router->resource('niveaux', \App\Admin\Controllers\NiveauController::class);
    $router->resource('personnels', \App\Admin\Controllers\PersonnelController::class);
    $router->resource('reglement_etudiants', \App\Admin\Controllers\reglement_etudiantController::class);
    $router->resource('role_utilisateurs', \App\Admin\Controllers\Role_utilisateurController::class);
    $router->resource('scolarites', \App\Admin\Controllers\ScolariteController::class);
    $router->resource('tranche_scolarites', \App\Admin\Controllers\Tranche_scolariteController::class);
   /* \Illuminate\Routing\Route::get('valider-pdg/{id}', [Bon_commandeokController::class, 'validerPDG']);
    \Illuminate\Routing\Route::get('valider-daf/{id}', [Bon_commandeokController::class, 'validerDAF']);
    \Illuminate\Routing\Route::get('valider-achats/{id}', [Bon_commandeokController::class, 'validerAchats']);
    \Illuminate\Routing\Route::get('valider-emetteur/{id}', [Bon_commandeokController::class, 'validerEmetteur']);*/
    /*Route::get('valider-pdg/{id}', [Bon_commandeokController::class, 'validerPDG']);
    Route::get('valider-daf/{id}', [Bon_commandeokController::class, 'validerDAF']);
    Route::get('valider-achats/{id}', [Bon_commandeokController::class, 'validerAchats']);
    Route::get('valider-emetteur/{id}', [Bon_commandeokController::class, 'validerEmetteur']);*/

    // Routes de validation
    $router->get('valider-pdg/{id}', [Bon_commandeokController::class, 'validerPDG']);
    $router->get('valider-daf/{id}', [Bon_commandeokController::class, 'validerDAF']);
    $router->get('valider-achats/{id}', [Bon_commandeokController::class, 'validerAchats']);
    $router->get('valider-emetteur/{id}', [Bon_commandeokController::class, 'validerEmetteur']);
});
