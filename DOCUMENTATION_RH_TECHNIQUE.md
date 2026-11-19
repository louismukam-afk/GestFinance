# Documentation technique du module Ressources Humaines

## 1. Objectif du module

Le module de gestion des ressources humaines couvre le cycle suivant :

1. Enregistrement du personnel.
2. Qualification du personnel : permanent ou vacataire.
3. Définition des plages horaires et emplois du temps.
4. Importation des pointages biométriques.
5. Consolidation des présences, absences, retards et heures réalisées.
6. Calcul des montants dus.
7. Calcul de la paie des permanents.
8. Gestion des sanctions, acomptes, rubriques de paie, retenues fiscales/sociales.
9. Génération des bulletins et états de paie.

Le module est principalement implémenté dans :

- `app/Http/Controllers/Admin/PersonnelController.php`
- `app/Http/Controllers/Admin/PlageHoraireController.php`
- `app/Http/Controllers/Admin/EmploiPermanentController.php`
- `app/Http/Controllers/Admin/CoursEnseignantController.php`
- `app/Http/Controllers/Admin/BiometrieHeureController.php`
- `app/Http/Controllers/Admin/DisciplinePersonnelController.php`
- `app/Http/Controllers/Admin/SalairePermanentController.php`
- `app/Http/Controllers/Admin/PaiePermanentController.php`

## 2. Données principales

### 2.1 Personnel

Table : `personnels`

Champs fonctionnels importants :

- `nom`
- `date_naissance`
- `lieu_naissance`
- `adresse`
- `sexe`
- `statut_matrimonial`
- `email`
- `telephone`
- `telephone_whatsapp`
- `numero_cnps`
- `numero_contribuable`
- `diplome`
- `niveau_etude`
- `domaine_formation`
- `date_recrutement`
- `nationalite`
- `type_personnel` : `permanent` ou `vacataire`
- `mode_horaire` : `strict` ou `souple`
- `categorie_horaire` : `standard`, `conseil_administration`, `chef_service`, `coordination`
- `horaire_travail` : `permanent_jour`, `permanent_soir`, `vacataire_jour`, `vacataire_soir`

Règle anti-doublon côté insertion :

- un personnel est considéré comme déjà existant si un autre enregistrement possède le même `nom` et la même `date_naissance`;
- l'email est également utilisé comme garde-fou si renseigné.

### 2.2 Plages horaires

Table : `plage_horaires`

Les plages définissent les blocs de temps exploitables par le module :

- `heure_debut`
- `heure_fin`
- `duree_payable`
- `type_plage` : `cours` ou `pause`
- `type_personnel` : `tous`, `permanent`, `vacataire`
- `periode_journee` : `jour` ou `soir`
- `format_plage`
- `ordre`
- `statut`

Règle de durée :

- si `duree_payable` est renseignée, elle sert de durée officielle;
- sinon la durée est calculée par différence entre `heure_debut` et `heure_fin`.

Formule :

```text
duree_prevue = duree_payable si renseignée
sinon duree_prevue = (heure_fin - heure_debut) en heures
```

### 2.3 Emplois permanents

Table : `emploi_permanents`

Un emploi permanent associe :

- un personnel permanent;
- une plage horaire;
- un ou plusieurs jours de semaine;
- éventuellement une année académique et une entité;
- une période de validité `date_debut` / `date_fin`.

À l'enregistrement, si plusieurs jours sont cochés, le système crée une ligne par jour.

### 2.4 Salaires permanents

Table : `salaires_permanents`

Chaque salaire contient :

- `id_personnel`
- `id_annee_academique`
- `id_entite`
- `montant_mensuel`
- `date_debut`
- `date_fin`
- `statut`

Le salaire journalier est calculé selon le nombre de jours du mois :

```text
salaire_journalier = montant_mensuel / nombre_de_jours_du_mois
```

Exemple :

```text
Salaire mensuel = 310 000
Mois de 31 jours
Salaire journalier = 310 000 / 31 = 10 000
```

## 3. Personnel permanent et personnel vacataire

### 3.1 Permanent

Un personnel permanent est payé sur la base d'un salaire mensuel.

Le contrôle biométrique sert à :

- calculer les heures prévues;
- calculer les heures réellement réalisées;
- identifier les retards;
- identifier les absences;
- déterminer les pénalités biométriques;
- alimenter la paie.

### 3.2 Vacataire / enseignant

Un vacataire est généralement lié à un cours programmé.

Le montant dû dépend des heures effectivement réalisées :

```text
montant_total = duree_realisee * montant_taux_horaire
```

Les heures réalisées sont stockées dans `heures_realisees_enseignants`.

## 4. Importation biométrique

### 4.1 Import

Table : `biometrie_imports`

Types d'import :

- `cours` : pointages liés aux cours/enseignants;
- `permanent` : pointages liés aux permanents;
- `manuel` : saisie manuelle des heures réalisées.

À l'import :

1. le fichier est stocké dans `public/uploads/biometrie`;
2. le système lit le fichier via PhpSpreadsheet;
3. si la lecture classique échoue, un lecteur legacy tente d'extraire les données;
4. chaque ligne exploitable devient un `biometrie_pointage`.

### 4.2 Association personnel / biométrie

Table : `biometrie_personnel_mappings`

Le système tente d'associer un pointage à un personnel de deux manières :

1. correspondance directe ou approchée du nom biométrique avec le nom du personnel;
2. mapping explicite par `nom_biometrie` ou `numero_biometrie`.

Si aucun personnel n'est trouvé, le pointage est compté dans `total_non_associees`.

## 5. Consolidation des présences permanentes

### 5.1 Processus global

Contrôleur : `BiometrieHeureController`

Action : consolidation d'un import permanent.

Étapes :

1. Réassocier les pointages non associés grâce aux mappings biométriques.
2. Supprimer les anciennes consolidations de l'import.
3. Charger les emplois permanents actifs qui croisent la période de l'import.
4. Pour chaque emploi et chaque date de la période :
   - vérifier si le jour correspond au jour programmé;
   - ignorer les jours payés non travaillés dans ce premier passage;
   - consolider la présence.
5. Ajouter les jours ouvrables sans emploi explicite à partir de la plage par défaut.
6. Ajouter les jours payés non travaillés : week-end et jours fériés.

### 5.2 Intervalle réel de présence

Pour un permanent, le système prend :

```text
heure_debut_reelle = premier pointage du jour
heure_fin_reelle   = dernier pointage du jour
```

Si le nombre de pointages est inférieur à 2 :

- aucun intervalle complet n'est trouvé;
- la journée est considérée absente ou incomplète;
- une observation est enregistrée.

### 5.3 Calcul en mode horaire souple

Un personnel est considéré souple si :

- `mode_horaire = souple`
- ou `categorie_horaire` est dans :
  - `conseil_administration`
  - `chef_service`
  - `coordination`

Calcul :

```text
temps_travaille = heure_fin_reelle - heure_debut_reelle
duree_realisee = min(duree_prevue, temps_travaille)
heures_manquantes = duree_prevue - duree_realisee
```

En mode souple, l'heure exacte d'arrivée est moins importante que le quota réalisé.

### 5.4 Calcul en mode horaire strict

En mode strict, un retard pénalise par heure entière commencée.

Calcul :

```text
minutes_retard = heure_debut_reelle - heure_debut_prevue si arrivée tardive
penalite_heures = ceil(minutes_retard / 60)
duree_realisee = max(duree_prevue - penalite_heures, 0)
```

Si le départ réel est avant l'heure prévue de fin :

```text
duree_realisee = min(duree_realisee, temps_reel_travaille)
```

Exemple :

```text
Prévu : 08h00 - 16h00, soit 8 h
Arrivée : 08h20
Sortie : 16h00
Retard = 20 min
Pénalité = ceil(20 / 60) = 1 h
Durée réalisée = 8 - 1 = 7 h
```

### 5.5 Calcul du montant dû permanent par jour

Le système utilise une référence de 8 heures par jour.

```text
salaire_journalier = salaire_mensuel / nombre_de_jours_du_mois
taux_horaire_permanent = salaire_journalier / 8
montant_du = taux_horaire_permanent * min(duree_realisee, 8)
penalite_montant = salaire_journalier - montant_du
```

Le statut de présence devient :

- `present` si `duree_realisee >= duree_prevue`;
- `partiel` si `duree_realisee > 0` mais inférieure à `duree_prevue`;
- `absent` si `duree_realisee = 0`;
- `jour_paye` pour week-end ou jour férié payé non travaillé.

### 5.6 Jours payés non travaillés

Le système considère comme jours payés non travaillés :

- les week-ends;
- les jours fériés camerounais fixes :
  - 1er janvier
  - 11 février
  - 1er mai
  - 20 mai
  - 15 août
  - 1er novembre
  - 25 décembre
- certains jours mobiles chrétiens calculés autour de Pâques;
- certains jours musulmans configurés par année dans le code.

Pour ces jours :

```text
duree_prevue = 0
duree_realisee = 0
montant_du = salaire_journalier
penalite_montant = 0
statut = jour_paye
```

## 6. Consolidation des heures enseignants / vacataires

### 6.1 Données sources

Le cours enseignant associe :

- un personnel;
- un programme de spécialité;
- une salle;
- un taux horaire;
- une période de validité;
- une ou plusieurs séances.

Chaque séance possède :

- un jour de semaine;
- une plage horaire.

### 6.2 Processus de consolidation

Pour chaque cours actif de la période importée :

1. Le système parcourt les dates comprises entre `date_debut` et `date_fin`.
2. Il vérifie les séances du jour correspondant.
3. Il détermine la plage prévue : début, fin, durée.
4. Il recherche les pointages du personnel sur la journée.
5. Il tente d'isoler une entrée et une sortie compatibles avec la plage.
6. Il calcule la durée réalisée et le montant.

### 6.3 Fenêtres de tolérance autour des plages

Pour trouver une entrée et une sortie :

```text
fenetre_entree = heure_debut_prevue +/- 30 minutes
fenetre_sortie = heure_fin_prevue +/- 30 minutes
```

Si aucune paire directe entrée/sortie n'est trouvée, le système reconstruit des intervalles de présence et choisit celui qui couvre le mieux la plage.

Les pointages pendant les plages de pause sont ignorés comme entrées.

### 6.4 Calcul des heures et montants

```text
duree_prevue = duree_payable de la plage ou différence heure_fin - heure_debut
duree_realisee = min(duree_prevue, heure_fin_reelle - heure_debut_reelle)
montant_total = duree_realisee * montant_taux_horaire
```

Statuts :

- `realise` si une durée positive est trouvée;
- `non_realise` sinon.

Si une partie de la séance n'est pas couverte :

```text
heures_non_comptabilisees = duree_prevue - duree_realisee
```

## 7. Saisie manuelle des heures

La saisie manuelle permet d'enregistrer une heure réalisée sans import biométrique.

Contrôles appliqués :

- la date de séance doit être dans la période de l'import manuel;
- la date doit être dans la période du cours enseignant;
- le jour choisi doit correspondre au jour programmé de la séance;
- l'heure de fin réelle doit être postérieure à l'heure de début réelle.

Calcul :

```text
heure_debut_reelle = max(heure_debut_saisie, heure_debut_prevue)
heure_fin_reelle = min(heure_fin_saisie, heure_fin_prevue)
duree_realisee = min(duree_prevue, heure_fin_reelle - heure_debut_reelle)
montant_total = duree_realisee * taux_horaire
```

## 8. Discipline automatique

Table : `discipline_personnels`

Les disciplines sont générées automatiquement pendant la consolidation biométrique.

### 8.1 Absence

Une absence est créée si :

- aucun pointage n'existe sur la journée;
- ou aucun pointage ne tombe dans la plage prévue.

Champs principaux :

- `type_discipline = absence`
- `duree_heures = duree_prevue`
- `statut = non_justifie`

### 8.2 Retard

Un retard est créé si :

- le personnel est en mode strict;
- l'heure réelle de début est postérieure à l'heure prévue.

Calcul :

```text
minutes_retard = heure_debut_reelle - heure_debut_prevue
```

Pour les personnels souples ou catégories spéciales, le retard automatique n'est pas généré.

### 8.3 Justification

Un utilisateur peut justifier une discipline en renseignant :

- un motif de justification;
- une date de justification;
- éventuellement des preuves : images ou PDF.

Le statut passe alors à `justifie`.

## 9. Paie des permanents

### 9.1 Préconditions

Pour générer la paie d'un permanent, il faut :

- un personnel de type `permanent`;
- un salaire permanent actif sur la période;
- éventuellement des présences consolidées si l'on veut appliquer la biométrie;
- les rubriques de paie;
- les paramètres fiscaux et sociaux.

### 9.2 Génération d'un bulletin

Contrôleur : `PaiePermanentController`

Processus :

1. Sélectionner les permanents à payer.
2. Récupérer leur salaire actif sur la période.
3. Consolider la biométrie pour déterminer :
   - salaire théorique;
   - pénalité biométrique.
4. Créer ou mettre à jour un bulletin brouillon.
5. Supprimer les anciennes lignes du bulletin.
6. Ajouter la ligne de salaire de base consolidé.
7. Ajouter les rubriques configurées pour le personnel.
8. Calculer les retenues système : CNPS/PVID, IRPP, CAC, etc.
9. Ajouter les pénalités biométriques.
10. Ajouter les acomptes et sanctions.
11. Calculer le net à payer.

### 9.3 Salaire théorique et pénalité biométrique

Si aucune présence permanente n'existe sur la période :

```text
salaire_theorique = salaire_contractuel
penalite_biometrie = 0
```

Si des présences existent, le système travaille par mois :

```text
taux_horaire = (salaire_mensuel / nombre_de_jours_du_mois) / 8
heures_non_realisees = somme(max(duree_prevue - duree_realisee, 0))
penalite_mois = heures_non_realisees * taux_horaire
penalite_mois = min(penalite_mois, salaire_mensuel)
```

Puis :

```text
salaire_theorique = somme(salaires_mensuels des mois concernés)
penalite_biometrie = somme(penalites_mois)
montant_du = salaire_theorique - penalite_biometrie
```

Important : dans le bulletin, le salaire de base utilisé est le `salaire_theorique`. La pénalité biométrique est ajoutée comme retenue.

### 9.4 Rubriques de paie

Table : `rubriques_paie`

Types :

- `gain`
- `retenue`

Modes de calcul :

- `fixe`
- `pourcentage`
- `kilometrage`
- `bareme`
- `manuel`

Bases possibles :

- `salaire_base`
- `brut`
- `taxable`
- `cotisable`
- `net`
- `irpp`

Calcul des rubriques :

```text
si mode = pourcentage :
    montant = base * taux / 100

si mode = kilometrage :
    montant = valeur * quantite

sinon :
    montant = valeur * max(quantite, 1)
```

Chaque ligne indique si elle est :

- imposable;
- cotisable.

Ces indicateurs servent aux bases fiscales/sociales.

### 9.5 Brut, taxable et cotisable

Après ajout des gains :

```text
brut_mensuel = somme des lignes dont sens = plus
salaire_taxable = somme des gains imposables
salaire_cotisable = somme des gains cotisables
```

### 9.6 CNPS / PVID

La retenue CNPS salariale utilise :

- la rubrique système `cnps_salarial`;
- ou les paramètres PVID applicables à la date du bulletin.

Calcul :

```text
base_cnps = min(salaire_cotisable, plafond_pvid)
cnps = base_cnps * taux_pvid / 100
```

Si aucun plafond n'est défini :

```text
base_cnps = salaire_cotisable
```

### 9.7 IRPP

L'IRPP est calculé sur :

```text
base_irpp = salaire_taxable - cnps
```

Le calcul utilise les tranches applicables dans `baremes_irpp`.

Pour chaque tranche :

```text
montant_tranche = min(base_irpp, montant_max) - montant_min
impot_tranche = montant_tranche * taux / 100
```

L'IRPP total est la somme des impôts par tranche.

### 9.8 CAC et autres retenues système

Pour les rubriques système `cac`, `rav`, `ccf`, `tdl` :

- si la base de calcul est `irpp`, la base est l'IRPP calculé;
- sinon la base est le salaire taxable.

Pour le CAC :

```text
cac = irpp * taux_cac / 100
```

Le taux CAC provient de `parametres_cac` applicable à la date du bulletin.

### 9.9 Acomptes

Table : `acomptes_salaire`

Un acompte est imputé sur un bulletin si :

- il appartient au même personnel;
- son statut est `actif`;
- il n'est pas déjà affecté à un autre bulletin;
- `periode_imputation` correspond au mois du bulletin.

Calcul :

```text
total_acomptes = somme des acomptes actifs du mois
```

Une ligne de retenue `acomptes` est ajoutée au bulletin.

### 9.10 Sanctions salariales

Table : `sanction_salaires`

Une sanction est imputée si :

- elle appartient au même personnel;
- elle est active;
- elle n'est pas déjà affectée à un autre bulletin;
- sa période d'application croise la période du bulletin.

Calcul :

```text
total_sanctions = somme des sanctions applicables
```

Une ligne de retenue `sanctions` est ajoutée au bulletin.

### 9.11 Net à payer

Calcul final :

```text
total_gains = somme des lignes sens = plus
total_retenues = somme des lignes sens = moins
net_a_payer = max(total_gains - total_retenues, 0)
solde_du = net_a_payer
```

Le bulletin contient notamment :

- `salaire_base`
- `penalite_biometrie`
- `brut_mensuel`
- `salaire_taxable`
- `salaire_cotisable`
- `total_gains`
- `total_retenues`
- `total_acomptes`
- `total_sanctions`
- `net_a_payer`
- `solde_du`

### 9.12 Validation du bulletin

Un bulletin généré est en statut `brouillon`.

À la validation :

- le statut passe à `valide`;
- les acomptes et sanctions utilisés sont liés au bulletin via `id_bulletin_paie`;
- cela évite de les réutiliser accidentellement dans un autre bulletin.

## 10. État de paie

L'état de paie consolide plusieurs bulletins sur une période.

Processus :

1. Sélectionner les bulletins selon la période, le statut, l'année académique et/ou l'entité.
2. Créer un `etat_paie`.
3. Créer une ligne par bulletin dans `lignes_etat_paie`.
4. Copier les agrégats du bulletin :
   - salaire de base;
   - total gains;
   - total retenues hors acompte/sanction/pénalité biométrie;
   - pénalité biométrie;
   - sanctions;
   - acomptes;
   - net à payer.
5. Stocker le détail dynamique des gains et retenues pour construire les colonnes de l'état.
6. Mettre à jour les totaux de l'état.

Calcul des totaux :

```text
nombre_employes = nombre de lignes
total_gains = somme lignes.total_gains
total_retenues = somme lignes.total_retenues
total_penalites = somme lignes.penalite_biometrie
total_sanctions = somme lignes.total_sanctions
total_acomptes = somme lignes.total_acomptes
total_net_a_payer = somme lignes.net_a_payer
```

## 11. Exports

Le module RH propose plusieurs exports :

- Liste du personnel en Excel.
- Fiche d'identification du personnel en PDF.
- Heures réalisées des enseignants en Excel/PDF.
- Bulletins de paie permanents en PDF.
- État de paie en PDF.

Les exports PDF utilisent `barryvdh/laravel-dompdf`.
Les exports Excel utilisent `maatwebsite/excel`.

## 12. Résumé des formules clés

### Salaire journalier permanent

```text
salaire_journalier = salaire_mensuel / nombre_de_jours_du_mois
```

### Taux horaire permanent

```text
taux_horaire_permanent = salaire_journalier / 8
```

### Montant dû journalier permanent

```text
montant_du = taux_horaire_permanent * min(duree_realisee, 8)
```

### Pénalité journalière permanente

```text
penalite_montant = salaire_journalier - montant_du
```

### Heures réalisées enseignant

```text
duree_realisee = min(duree_prevue, heure_fin_reelle - heure_debut_reelle)
```

### Montant enseignant / vacataire

```text
montant_total = duree_realisee * taux_horaire
```

### Pénalité biométrique mensuelle

```text
heures_non_realisees = somme(max(duree_prevue - duree_realisee, 0))
penalite = heures_non_realisees * ((salaire_mensuel / jours_du_mois) / 8)
penalite = min(penalite, salaire_mensuel)
```

### Net à payer

```text
net_a_payer = max(total_gains - total_retenues, 0)
```

## 13. Points de vigilance techniques

1. Les imports biométriques doivent être consolidés avant de générer une paie prenant en compte les pénalités.
2. Les pointages non associés doivent être corrigés via le mapping biométrique, puis la consolidation doit être relancée.
3. Les acomptes et sanctions sont liés au bulletin au moment de la validation.
4. Les catégories `conseil_administration`, `chef_service` et `coordination` sont traitées comme horaires souples.
5. Les jours payés non travaillés ajoutent du montant dû sans durée réalisée.
6. Les rubriques imposables/cotisables influencent directement l'IRPP, la CNPS/PVID et les autres retenues.
7. Les bulletins existants sont recréés sur la même période/personnel via `updateOrCreate`, puis leurs lignes sont recalculées.

