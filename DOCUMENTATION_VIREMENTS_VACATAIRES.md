# Etats de virement des enseignants

## Acces

Ressources humaines > Etats de virement enseignants, ou Historique des
consolidations > Etat de virement sur un import consolide.

Les routes `virements_vacataires.index`, `.create`, `.store`, `.show` et `.pdf`
sont authentifiees et soumises aux permissions existantes. Les roles non
administrateurs doivent recevoir ces permissions.

## Preparation

1. Choisir un import de cours consolide, l'entite des cours et la banque.
2. Choisir independamment les groupes Banque choisie, Autres banques,
   Sans banque ou sans numero de compte. La banque choisie reste la banque
   qui emet l'ordre, meme si seul un des deux autres groupes est coche.
3. Cocher les beneficiaires. Le compte provient de `personnels.niveau_etude`.
4. Rectifier eventuellement le compte pour cet ordre uniquement. Cette saisie
   ne modifie pas la fiche du personnel. Un compte vide impose une confirmation.
5. Cocher Imputer et saisir les frais : 3500 FCFA proposes, personnalisables.
   Sans cette coche, aucun frais n'est retenu.
6. Saisir le compte de l'entite a debiter, l'agence, le motif libre, la date,
   le lieu et le signataire. Choisir le modele ISH ou CEF.
7. Confirmer et valider. L'etat definitif est archive et son PDF est disponible.

Un etat n'execute aucun transfert bancaire. Il produit le document a transmettre
a la banque. Le PDF porte les montants apres deduction des frais et le total
en chiffres et en lettres. Aucun compte de l'entite n'est pre-rempli.

## Calculs

L'entite provient de `heures_realisees_enseignants.id_entite`, issu du cours
consolide, et non de l'affectation administrative principale du personnel.

La consolidation et l'impot restent calcules par enseignant et par mois.
Pour plusieurs entites, le brut fiscal mensuel est reparti proportionnellement
aux montants positifs apres sanctions calcules dans chaque entite. L'impot
mensuel est reparti proportionnellement a ces bases. Les centimes residuels
suivent la methode des plus forts restes, ordre des entites stable.
La somme des entites reste egale a la consolidation fiscale globale, meme
lorsqu'une sanction excede la remuneration d'une seule entite.

Les mois ne se compensent pas. Une ligne regroupe les mois de l'import pour
un enseignant dans une entite. Les frais sont deduits une seule fois par ligne.
Ils doivent etre strictement inferieurs au net apres impot.

## Archivage

Migration : `2026_11_19_000004_create_virements_vacataires_tables.php`.
Tables : `virements_vacataires` et `lignes_virement_vacataires`.

La validation recalcule les montants et refuse une preparation devenue obsolete.
La transaction verrouille l'entite et l'import. Un enseignant dans une meme
entite ne peut pas etre valide deux fois sur des periodes chevauchantes,
y compris a partir de deux imports ou pour deux banques differentes.
Une contrainte unique protege aussi le triplet import / entite / personnel.
Plusieurs ordres peuvent donc etre valides successivement pour une meme
entite et un meme import, sur des selections distinctes. Un enseignant deja
inclus dans un ordre valide disparait de la preparation pour cette entite
et cet import, quelle que soit la banque emettrice. Il reste eligible dans
les autres entites ou il a enseigne. Le formulaire affiche les nombres
d'enseignants a net positif, deja inclus et restant a traiter dans l'entite.

Les groupes de beneficiaires sont exclusifs : une banque affectee avec un
compte renseigne classe l'enseignant dans Banque choisie ou Autres banques.
Une affectation bancaire absente ou un numero de compte vide le classe dans
Sans banque ou sans numero de compte. Les filtres sont reverifies au serveur
et ne changent ni les montants ni les ordres deja archives.

Noms, banques, comptes, taux et montants sont figes dans l'etat. Les PDF ne
sont pas recalcules lors d'un changement de fiche, de taux ou de consolidation.
Les archives n'ont pas de suppression en cascade depuis les sources.
Aucune modification ou suppression des etats definitifs n'est exposee.

Les images ISH et CEF proviennent des modeles Word fournis. Le PNG CEF a ete
converti en JPEG sur fond blanc pour ne pas imposer PHP GD. Les montants en
lettres fonctionnent aussi sans extension intl.

## Tests

`tests/Feature/VirementVacataireTest.php` et
`tests/Unit/PreparationVirementVacataireTest.php` utilisent SQLite en memoire
et ne creent aucun paiement dans la base de production.

## Historique et bulletins par import

La page `emploi-du-temps/virements-vacataires/historique` propose les filtres
date de l'ordre, banque, entite, numero d'import, nom du beneficiaire et motif.
Les libelles de banque et d'entite proviennent des archives, meme si les
fiches de reference ont change. Chaque ordre reste consultable et imprimable.
Permission supplementaire : `virements_vacataires.historique`.

Les routes `bulletins_vacataires.index`, `.show` et `.pdf` donnent acces aux
bulletins d'un import de cours consolide. Le PDF peut contenir tous les
enseignants ou un seul via `id_personnel`. Un bulletin regroupe les entites
d'un enseignant et detaille les mois, heures, sanctions, impots et frais.

Les lignes deja validees reprennent exclusivement les instantanes des ordres
archives. Les autres lignes restent provisoires, sans frais non valides.
Un bulletin peut etre partiellement valide si certaines entites n'ont pas
encore d'ordre. Generer un PDF ne valide pas de paiement et n'ecrit pas en base.

Les nouveaux ordres archivent egalement les heures et sanctions dans leur
champ JSON `details`. Les anciens ordres sans ces informations affichent
un tiret et une mention explicite plutot que des heures recalculees.
Ce changement ne necessite aucune nouvelle migration.
