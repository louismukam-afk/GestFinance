# Documentation du projet GestFinance

Derniere mise a jour: 17 juillet 2026

## 1. Resume executif

GestFinance est une application web Laravel destinee a la gestion financiere et administrative d'un etablissement de formation/ecole. Le projet couvre aujourd'hui:

- la gestion des referentiels pedagogiques: annees academiques, cycles, filieres, niveaux, specialites, scolarites, tranches, frais, matieres et programmes;
- la facturation des etudiants: factures de scolarite, frais, rattrapage, reductions, reglements et PDF;
- la structuration budgetaire: budgets, lignes budgetaires d'entree/sortie, elements, donnees budgetaires et donnees de ligne;
- les bons de commande, circuits de validation, elements de bon et documents PDF;
- les decaissements, retours de caisse, transferts, caisses et banques;
- les entrees speciales et remboursements/echeances;
- les etats et reportings: atterrissage budgetaire, etats de caisse, factures/reglements, exports PDF/Excel;
- les modules RH: personnel, fonctions, emploi du temps, biometrie, heures realisees, salaires permanents, paie et sanctions;
- le controle d'acces applicatif par roles, permissions de routes et journal d'activite.

L'application combine une interface Laravel classique (`resources/views`) avec une zone `laravel-admin` sous `/admin`.

## 2. Socle technique

### Framework et dependances

- PHP: `^8.1`
- Laravel: `^10.10`
- Authentification: `laravel/ui`
- API/session: `laravel/sanctum`
- Administration: `encore/laravel-admin`
- PDF: `barryvdh/laravel-dompdf`
- Excel: `maatwebsite/excel` version `3.1.47`
- Base ORM: Eloquent
- Vues: Blade
- Frontend: Bootstrap/jQuery/DataTables selon les vues existantes

### Fichiers importants

- Routes web principales: `routes/web.php`
- Controleurs metier: `app/Http/Controllers/Admin` et `app/Http/Controllers/Budget`
- Modeles Eloquent: `app/Models`
- Vues Blade: `resources/views`
- Migrations: `database/migrations`
- Middleware d'acces: `app/Http/Middleware/CheckRoutePermission.php`
- Journalisation d'activite: `app/Http/Middleware/LogUserActivity.php`

## 3. Architecture fonctionnelle

### 3.1 Authentification et acces

Le projet utilise l'authentification Laravel avec les routes classiques:

- `login`
- `logout`
- `register`
- `password/reset`

Il existe aussi une administration `laravel-admin` sous `/admin`.

Un module interne de controle d'acces existe:

- gestion des roles;
- synchronisation des routes;
- affectation des roles aux utilisateurs;
- affectation de permissions aux roles;
- journal d'activite.

Tables et modeles concernes:

- `users`
- `roles`
- `route_permissions`
- `role_route_permission`
- `role_user`
- `activity_logs`

Controleur principal:

- `Admin\AccessControlController`

Middleware associes:

- `CheckRoutePermission`
- `EnsureSuperAdmin`
- `LogUserActivity`

## 4. Referentiels de base

Le projet gere plusieurs referentiels administratifs et pedagogiques.

### 4.1 Referentiels financiers

Objets principaux:

- banques;
- caisses;
- entites;
- budgets;
- lignes budgetaires d'entree;
- lignes budgetaires de sortie;
- elements de lignes budgetaires;
- donnees budgetaires;
- donnees de lignes budgetaires.

Controleurs principaux:

- `Admin\BanqueController`
- `Admin\CaisseController`
- `Admin\EntiteController`
- `Budget\BudgetController`
- `Budget\LigneBudgetaireEntreeController`
- `Budget\LigneBudgetaireSortieController`
- `Budget\ElementLigneBudgetaireEntreeController`
- `Budget\ElementLigneBudgetaireSortieController`
- `Budget\DonneeBudgetaireEntreeController`
- `Budget\DonneeBudgetaireSortieController`
- `Budget\DonneeLigneBudgetaireEntreeController`
- `Budget\DonneeLigneBudgetaireSortieController`

Relations metier:

```text
Budget
  -> lignes budgetaires entree/sortie
    -> elements de lignes
      -> donnees budgetaires
        -> donnees ligne budgetaire
```

Les donnees budgetaires servent ensuite aux factures, reglements, bons de commande, decaissements et etats.

### 4.2 Referentiels pedagogiques

Objets principaux:

- annees academiques;
- cycles;
- filieres;
- niveaux;
- specialites;
- scolarites;
- tranches de scolarite;
- frais;
- matieres;
- programmes par specialite;
- groupes de matieres.

Controleurs principaux:

- `Admin\AnneeAcademiqueController`
- `Admin\CycleController`
- `Admin\FiliereController`
- `Admin\NiveauController`
- `Admin\SpecialiteController`
- `Admin\ScolariteController`
- `Admin\TrancheScolariteController`
- `Admin\FraisController`
- `Admin\MatiereController`
- `Admin\ProgrammeSpecialiteController`
- `Admin\GroupeMatiereController`

Utilisation:

- les scolarites definissent les montants de formation par cycle/filiere/niveau/specialite/annee;
- les tranches structurent les echeances attendues;
- les frais servent aux factures non scolaires;
- les matieres et programmes servent notamment aux factures de rattrapage.

## 5. Gestion des etudiants, factures et paiements

### 5.1 Etudiants

Le module etudiant permet:

- creation et modification d'etudiants;
- import Excel/CSV;
- telechargement d'un modele d'import;
- gestion du matricule;
- acces aux factures d'un etudiant.

Controleur:

- `Admin\EtudiantController`

Modeles:

- `Etudiant`
- `annee_academique`
- `cycle`
- `filiere`
- `niveau`
- `specialite`

### 5.2 Factures etudiants

Le module facture gere trois familles:

- `type_facture = 1`: scolarite;
- `type_facture = 0`: frais;
- `type_facture = 2`: rattrapage.

Controleur principal:

- `Admin\FactureEtudiantController`

Vue principale:

- `resources/views/Admin/FactureEtudiant/index.blade.php`

Fonctions principales:

- afficher les factures d'un etudiant;
- creer une facture de scolarite ou de frais;
- modifier une facture;
- supprimer une facture si aucun reglement n'est rattache;
- generer/afficher/telecharger le PDF;
- charger les listes dependantes en AJAX:
  - cycle/filiere/annee vers niveaux, specialites et scolarites;
  - budget vers lignes;
  - ligne vers elements;
  - element vers donnees budgetaires;
  - donnees budgetaires vers donnees de ligne.

Regles importantes:

- une facture de scolarite prend le montant total de scolarite + inscription;
- une facture de frais prend le montant du frais choisi;
- une facture de scolarite choisit automatiquement la premiere tranche;
- les montants previsionnels de la donnee budgetaire et de la donnee ligne sont recalcules apres creation, modification ou suppression;
- la suppression est bloquee s'il existe des reglements rattaches.

Corrections recentes:

- la modification d'une facture de type `Frais` ne demande plus une `id_scolarite`;
- le formulaire masque/desactive correctement les champs non applicables entre `Scolarite` et `Frais`.

### 5.3 Reductions de factures

Le module permet:

- enregistrer une reduction sur facture;
- filtrer et lister les reductions;
- exporter en Excel et PDF.

Controleur:

- `Admin\ReductionFactureController`

Modele:

- `reduction_facture`

Effet metier:

- le modele `facture_etudiant` expose un montant net:

```text
montant_net_facture = montant_total_facture - montant_reduction
```

### 5.4 Reglements etudiants

Le module reglement permet:

- afficher les reglements d'une facture;
- creer un reglement depuis une facture;
- modifier/supprimer un reglement;
- generer/telecharger un PDF de reglement;
- rattacher le paiement a une caisse/banque et a l'imputation budgetaire.

Controleur:

- `Admin\ReglementEtudiantController`

Modele:

- `reglement_etudiant`

Effets metier:

- le reste a payer d'une facture est calcule a partir du montant net et des reglements;
- les ecritures de reglement alimentent les etats comptables et budgetaires.

### 5.5 Factures de rattrapage

Le module permet de creer une facture supplementaire depuis une facture etudiant existante pour des matieres de rattrapage.

Controleur:

- `Admin\FactureRattrapageController`

Modeles:

- `facture_etudiant`
- `FactureRattrapageLigne`
- `Matiere`
- `ProgrammeSpecialite`

Flux:

```text
Facture etudiant source
  -> creation facture de rattrapage
    -> ajout des matieres, quantites, prix unitaires
      -> facture type 2
```

## 6. Budget, engagements et execution

### 6.1 Budgets

Le module budget gere:

- creation et modification de budgets;
- liste et detail;
- export PDF et Excel;
- export d'un budget individuel.

Controleur:

- `Budget\BudgetController`

Modele:

- `Budget`

### 6.2 Lignes, elements et donnees budgetaires

Le budget est decompose en:

- lignes budgetaires d'entree;
- lignes budgetaires de sortie;
- elements de ligne;
- donnees budgetaires;
- donnees de ligne budgetaire.

Ces objets structurent l'imputation des factures, reglements, bons, decaissements et etats.

Exports disponibles:

- budgets: PDF/Excel;
- lignes budgetaires entree/sortie: PDF/Excel global et individuel;
- donnees budgetaires entree/sortie: PDF/Excel;
- donnees ligne entree/sortie: PDF/Excel;
- elements budgetaires entree/sortie: Excel.

Corrections recentes:

- ajout des exports Excel serveur pour:
  - elements de lignes budgetaires d'entree;
  - elements de lignes budgetaires de sortie;
- correction de la route `element_sorties.index` pour pointer vers `indexElements`;
- nettoyage du cache de routes et vues apres correction.

## 7. Bons de commande et validations

### 7.1 Bons de commande

Le module bons de commande permet:

- creation et mise a jour de bons;
- gestion des elements/lignes du bon;
- generation PDF;
- export Excel;
- suivi par statut;
- recherche.

Controleurs:

- `Admin\Bon_commandeController`
- `Admin\ElementBonCommandeController`
- `Admin\MesBonCommandeController`
- `Admin\EtatBonCommandeController`

Modeles:

- `bon_commande`
- `bon_commandeok`
- `element_bon_commande`

### 7.2 Circuit de validation

Le circuit de validation gere plusieurs niveaux, notamment:

- emetteur;
- achats;
- DAF;
- PDG.

Controleur:

- `Admin\BonValidationController`

Fonctions principales:

- lister les bons par niveau de validation;
- valider;
- refuser;
- imputer au niveau DAF;
- exporter en PDF.

Des notifications existent:

- `BonCommandeValidatorNotification`
- `BonCommandeActivityNotification`

## 8. Decaissements, caisses, banques et transferts

### 8.1 Decaissements

Le module decaissement permet:

- enregistrer un decaissement lie a un bon;
- imputer le decaissement sur les lignes budgetaires;
- suivre les montants realises, restes et statuts;
- produire des recus et PDF;
- afficher des reportings;
- suivre les bons finances/non finances.

Controleur:

- `Budget\DecaissementController`

Modele:

- `decaissement`

### 8.2 Retours de caisse

Permet de restituer/retourner une partie de montant lie a un decaissement.

Controleur:

- `Budget\RetourCaisseController`

Modele:

- `retour_caisse`

Fonctions:

- creation;
- listing;
- liste personnelle;
- detail decaissement;
- export PDF.

### 8.3 Transferts de caisse et banque

Le projet gere les transferts entre caisses et/ou banques.

Controleur:

- `Admin\TransfertCaisseController`

Modele:

- `Transfert_caisse`

Fonctions:

- creation, modification, suppression;
- impression d'un transfert;
- impression de liste.

### 8.4 Affectations caisse/banque

Les utilisateurs peuvent etre rattaches a des caisses ou banques.

Tables:

- `caisse_user`
- `banque_user`

Controleurs:

- `Admin\CaisseController`
- `Admin\BanqueController`

## 9. Entrees speciales et remboursements

Le module `EntreeSpecialeController` gere des entrees financieres hors facturation standard.

Fonctions:

- creation et modification d'une entree speciale;
- affichage et impression;
- echeances;
- rappels;
- remboursements;
- paiement/annulation d'echeance;
- rattachement caisse/banque.

Modeles:

- `entree_speciale`
- `entree_speciale_echeance`

Ces donnees peuvent etre prises en compte dans les etats budgetaires.

## 10. Etats, reporting et exports

### 10.1 Etats etudiants et factures

Modules:

- etat de scolarite etudiants;
- factures avec reglements;
- situation etudiant;
- reductions de factures;
- factures/reglements comptables.

Controleurs:

- `Admin\EtatEtudiantScolariteController`
- `Admin\EtatComptableController`
- `Admin\ReportingController`

Exports:

- PDF;
- Excel;
- vues imprimables.

### 10.2 Etats budgetaires

Modules:

- atterrissage budgetaire;
- etat global budgetaire;
- etat de caisse;
- mon etat de caisse;
- disponibilite des caisses;
- sorties realisees/non financees.

Controleur:

- `Budget\EtatSortieController`

Exports:

- PDF;
- Excel.

## 11. Ressources humaines, emploi du temps et paie

### 11.1 Personnel

Le projet gere:

- personnels;
- fonctions;
- affectations a des entites;
- discipline/sanctions;
- import de personnel;
- modele d'import.

Controleurs:

- `Admin\PersonnelController`
- `Admin\FonctionController`
- `Admin\PersonnelEntiteController`
- `Admin\DisciplinePersonnelController`

### 11.2 Emploi du temps et cours

Objets:

- salles;
- plages horaires;
- taux horaires;
- cours enseignants;
- seances de cours;
- emplois permanents.

Controleurs:

- `Admin\SalleController`
- `Admin\PlageHoraireController`
- `Admin\TauxHoraireController`
- `Admin\CoursEnseignantController`
- `Admin\EmploiPermanentController`
- `Admin\EmploiTempsController`

### 11.3 Biometrie et heures realisees

Le projet gere l'import de donnees biometriques et la consolidation:

- imports biometrie;
- pointages;
- mapping personnel;
- heures realisees enseignants;
- presence permanente;
- decompte heures;
- exports PDF/Excel.

Controleur:

- `Admin\BiometrieHeureController`

### 11.4 Salaires et paie

Objets:

- salaires permanents;
- rubriques de paie;
- configuration de rubriques par personnel;
- bulletins de paie;
- lignes de bulletin;
- acomptes;
- baremes IRPP;
- parametres CAC;
- parametres PVID;
- etats de paie.

Controleurs:

- `Admin\SalairePermanentController`
- `Admin\PaiePermanentController`

Fonctions:

- generation de bulletins;
- calcul et export PDF;
- etats de paie;
- parametres fiscaux et sociaux;
- sanctions/acompte.

## 12. Imports et exports

### Imports

Imports identifies:

- etudiants;
- personnels;
- matieres.

Classes:

- `EtudiantsImport`
- `PersonnelsImport`
- `MatieresImport`

### Exports Excel

Le projet utilise `maatwebsite/excel`. Exports identifies:

- bons;
- budgets;
- lignes budgetaires;
- donnees budgetaires;
- factures;
- factures/reglements;
- etats de caisse;
- etats budgetaires;
- heures realisees;
- reductions;
- templates d'import.

### Exports PDF

Le projet utilise `barryvdh/laravel-dompdf`.

PDF identifies:

- factures;
- reglements;
- bons de commande;
- elements de bons;
- budgets;
- lignes budgetaires;
- donnees budgetaires;
- decaissements/recus;
- retours caisse;
- etats comptables;
- etats de paie;
- etats RH/biometrie.

## 13. Donnees principales

Tables metier principales reperees dans les migrations:

### Administration

- `users`
- `roles`
- `route_permissions`
- `activity_logs`
- tables `laravel-admin`

### Pedagogie

- `annee_academiques`
- `cycles`
- `filieres`
- `niveaux`
- `specialites`
- `scolarites`
- `tranche_scolarites`
- `frais`
- `etudiants`
- `matieres`
- `programme_specialites`
- `groupe_matieres`
- `groupe_matiere_lignes`

### Finance et budget

- `banques`
- `caisses`
- `entites`
- `budgets`
- `ligne_budgetaire_entrees`
- `ligne_budgetaire_sorties`
- `element_ligne_budgetaire_entrees`
- `element_ligne_budgetaire_sorties`
- `donnee_budgetaire_entrees`
- `donnee_budgetaire_sorties`
- `donnee_ligne_budgetaire_entrees`
- `donnee_ligne_budgetaire_sorties`

### Etudiants et encaissements

- `facture_etudiants`
- `reglement_etudiants`
- `reduction_factures`
- `facture_rattrapage_lignes`

### Bons, sorties et caisse

- `bon_commandeoks`
- `element_bon_commandes`
- `decaissements`
- `retour_caisses`
- `transfert_caisses`
- `entree_speciales`
- `entree_speciale_echeances`

### RH et paie

- `personnels`
- `fonctions`
- `personnel_entites`
- `discipline_personnels`
- `salaires_permanents`
- `salles`
- `taux_horaires`
- `plage_horaires`
- `cours_enseignants`
- `seance_cours`
- `biometrie_imports`
- `biometrie_pointages`
- `heures_realisees_enseignants`
- `presence_permanents`
- `rubriques_paie`
- `config_rubriques_personnel`
- `bulletins_paie`
- `lignes_bulletin_paie`
- `acomptes_salaire`
- `baremes_irpp`
- `parametres_cac`
- `parametres_pvid`
- `etats_paie`
- `lignes_etat_paie`

## 14. Workflows principaux

### 14.1 Facturation etudiant

```text
Creer referentiels pedagogiques
  -> creer scolarite/frais/tranches
    -> creer etudiant
      -> creer facture
        -> generer PDF facture
          -> enregistrer reglements
            -> suivre reste a payer / etats comptables
```

### 14.2 Execution budgetaire de sortie

```text
Creer budget
  -> creer lignes budgetaires de sortie
    -> creer elements et donnees budgetaires
      -> creer bon de commande
        -> validation emetteur/achats/DAF/PDG
          -> decaissement
            -> retour caisse eventuel
              -> etats budgetaires et caisse
```

### 14.3 Encaissements et entrees

```text
Budget entree
  -> lignes/elements/donnees entree
    -> factures etudiants ou entrees speciales
      -> reglements/echeances
        -> etats d'encaissement
```

### 14.4 RH et paie

```text
Personnel + fonctions + entites
  -> emplois/plages/cours/biometrie
    -> heures realisees ou presences
      -> salaires/bulletins/etat de paie
```

## 15. Points d'attention techniques

### 15.1 Duplication et fichiers historiques

Le projet contient plusieurs fichiers semblant etre des copies ou versions historiques:

- `FactureEtudiantController1.php`
- `ReglementEtudiantController1.php`
- `EtatEtudiantScolariteController1.php`
- `ElementLigneBudgetaireSortieController1.php`
- `test.php`
- `test_facture.php`
- vues suffixees `index1`, `edit1`, etc.

Ces fichiers peuvent etre utiles comme sauvegardes, mais ils rendent la maintenance plus difficile. Avant suppression, verifier les routes actives.

### 15.2 Encodage

Certains fichiers contiennent des caracteres mal encodes visibles dans les libelles (`Ã©`, `âœ`, etc.). Cela indique probablement un melange d'encodage UTF-8/Windows-1252. Les vues fonctionnent, mais les libelles affiches peuvent etre affectes.

### 15.3 Routes nombreuses

`php artisan route:list` montre environ 719 routes. Cela inclut:

- routes applicatives;
- routes `laravel-admin`;
- routes d'authentification;
- routes Ignition en environnement de developpement.

### 15.4 Cache Laravel

Apres modification de routes ou vues, utiliser:

```bash
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## 16. Installation locale

### Prerequis

- PHP 8.1 ou plus;
- Composer;
- Node.js/npm si recompilation frontend necessaire;
- MySQL/MariaDB via XAMPP;
- extensions PHP usuelles Laravel;
- dependances Composer installees.

### Commandes typiques

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Dans ce poste, le projet est situe ici:

```text
C:\xampp\htdocs\GestFinance
```

Avec XAMPP, l'acces peut aussi se faire via Apache si le virtual host ou le dossier public est configure.

## 17. Etat actuel des corrections recentes

Corrections effectuees recemment dans le projet:

1. Factures etudiants
   - correction de la modification des factures de type `Frais`;
   - `id_scolarite` n'est plus exige pour les frais;
   - les champs caches du formulaire sont correctement actives/desactives;
   - verification PHP OK.

2. Elements budgetaires et Excel
   - ajout export Excel serveur pour elements budgetaires d'entree;
   - ajout export Excel serveur pour elements budgetaires de sortie;
   - ajout des vues Excel correspondantes;
   - ajout de boutons Excel dans les listes;
   - correction de la route `element_sorties.index`;
   - nettoyage `route:clear` et `view:clear`;
   - verification des routes OK.

Fichiers modifies/ajoutes recemment:

- `app/Http/Controllers/Admin/FactureEtudiantController.php`
- `resources/views/Admin/FactureEtudiant/index.blade.php`
- `app/Http/Controllers/Budget/ElementLigneBudgetaireEntreeController.php`
- `app/Http/Controllers/Budget/ElementLigneBudgetaireSortieController.php`
- `resources/views/Budget/element_entrees/index.blade.php`
- `resources/views/Budget/element_sorties/index.blade.php`
- `resources/views/Budget/element_entrees/excel.blade.php`
- `resources/views/Budget/element_sorties/excel.blade.php`
- `routes/web.php`

## 18. Conclusion

A ce stade, GestFinance est une application de gestion integree pour un etablissement, centree sur:

- la scolarite et les etudiants;
- la facturation et les encaissements;
- le budget et l'execution financiere;
- les bons de commande et validations;
- les caisses, banques et decaissements;
- les etats comptables/budgetaires;
- les ressources humaines, horaires, biometrie et paie;
- le controle d'acces et l'audit.

Le projet est fonctionnel mais volumineux. Les prochaines ameliorations conseillees sont:

- nettoyer les anciens fichiers `*1.php`, `test*` et vues dupliquees;
- corriger l'encodage des textes;
- regrouper les routes par fichiers (`routes/admin.php`, `routes/budget.php`, `routes/rh.php`);
- ajouter des tests automatiques sur les workflows critiques: facture, reglement, bon, decaissement, paie;
- documenter les roles et permissions attendus par profil utilisateur.
