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

## Selection et nettoyage des pointages

La page de decompte masque les tableaux de detail et de totaux tant qu'aucun
import existant n'est selectionne par Visualiser. Les identites biometriques,
les pointages non associes et les exports de la selection restent limites
a cet import. Un identifiant supprime ou appartenant aux permanents est refuse.

Le bouton Nettoyer les donnees sans import demande confirmation et appelle
la route DELETE `biometrie_heures.nettoyer_orphelins`, soumise aux permissions
habituelles. Il supprime uniquement les pointages bruts et les heures
consolidees dont l'import n'existe plus, y compris les references nulles.
Il ne supprime ni les imports existants, ni les correspondances manuelles,
ni les archives de virement. Aucune suppression n'est automatique a l'ouverture.

La suppression future d'un import retire ses pointages et heures dans la
meme transaction, puis revient a la liste. Les exports excluent egalement
les consolidations orphelines. Aucune migration supplementaire n'est requise.

## Saisie manuelle et requetes apres paiement

La saisie manuelle propose tous les imports enseignants (biometriques,
manuels et dossiers de requete), et non plus seulement les imports manuels.
Charger cet import affiche les cours de sa periode, y compris les anciens
cours. Les imports des permanents sans cours restent exclus.

Toute correction manuelle passe desormais par un dossier de requete.
L'import initial reste consultable mais n'est plus modifiable par ce formulaire.
Apres validation pour un enseignant, le dossier est protege : une nouvelle
requete est necessaire pour une nouvelle decision. Le formulaire Nouvelle
requete sur les heures et sanctions demande l'import initial consolide,
le mois et le motif/decision.
La route POST `biometrie_heures.requetes.store` suit les permissions RH
habituelles. Chaque dossier conserve ces references et sa propre consolidation.

La premiere correction d'un enseignant recopie ses heures du mois, toutes
entites confondues, depuis le dernier dossier deja valide ou l'import initial.
Les heures source et les ordres archives ne sont pas modifies. La decision
de lever une sanction supplementaire est distincte des heures travaillees :
elle ne transforme pas une absence en heures realisees. La retenue liee aux
heures non travaillees reste applicable, sauf correction effective des heures.

Le dossier produit une consolidation revisee, puis un etat fiscal, un ordre
de virement et un bulletin complementaires. Pour chaque enseignant/entite,
le complement est le droit revise moins le montant de l'ordre initial pour
ce mois et les complements deja valides. Le taux d'impot est celui archive
dans l'ordre initial. Pour un enseignant sans ordre initial, la requete
est aussi autorisee, que son montant initial soit nul ou positif : le montant
deja couvert est zero. Le taux de sa fiche (ou le taux par defaut) est applique
avant le premier ordre correctif, puis le taux archive dans cet ordre est
conserve pour les autres entites et dossiers du mois. Le premier ordre apres
requete couvre alors la totalite du droit revise non encore paye, pas seulement
la difference avec un montant initial qui n'avait jamais ete verse.
Les anciens frais de transfert ne sont pas rembourses
automatiquement. De nouveaux frais restent soumis aux controles ordinaires.
Les enseignants doivent avoir des heures consolidees dans l'import et le
mois choisis. Les doublons sur un autre import de periode chevauchante sont
refuses ; seuls l'import parent et ses dossiers de requete partagent ce suivi.
Un second dossier identique ne cree donc pas un nouveau montant a payer.
Un solde negatif bloque le paiement de cet enseignant pour examen, y compris
si une autre entite presente un complement positif. Aucune reprise ni ecriture
comptable automatique n'est generee.

L'historique filtre les ordres initiaux/apres requete, le mois, la banque et
l'entite. Le filtre d'import retrouve aussi les correctifs de cet import.
Les totaux net, impot et frais couvrent toute la selection, pas seulement la
page affichee. Il s'agit du suivi des etats par banque et entite, sans
decaissement dans la comptabilite budgetaire ni execution de transfert bancaire.

Deploiement : executer la migration
`2026_11_20_000001_add_requetes_vacataires.php` et
`2026_11_20_000002_add_type_requete_to_heures.php`, puis vider les caches Laravel.
Elle ajoute les references des dossiers, la nature des ordres et la decision
de sanction. Les anciens ordres conservent la nature initiale par defaut.
Les tests de requete utilisent exclusivement SQLite en memoire.

Apres une erreur de saisie, le contexte recharge correspond a l'import
effectivement envoye et non a un ancien identifiant dans l'URL. Changer le
selecteur desactive une decision de sanction appartenant a un autre dossier.
Le formulaire distingue trois decisions :
- Absence justifiee : les heures existantes sont conservees au serveur,
  meme si des horaires sont envoyes par erreur. Seule la sanction est levee.
- Presence attestee par releve administratif : horaires et justification
  obligatoires, heures plafonnees a la plage historique de la seance.
- Pointage incomplet : meme reconnaissance d'horaires attestes, avec
  conservation de la nature particuliere de la requete pour l'audit.

Une presence reconnue retire la sanction correspondant aux heures reconnues
et ajoute leur remuneration. Exemple, avant impot : 2 h a 3 000 FCFA/h
donnent 6 000 FCFA de restitution seule, ou 12 000 FCFA si les 2 h sont reconnues
comme travaillees. Les sanctions restant justifiees sur les autres heures
continuent de s'appliquer. Le plafond mensuel a zero du salaire initial reste
pris en compte : le paiement est le droit revise moins les ordres valides,
et non une addition aveugle des sanctions theoriques.

La route GET `biometrie_heures.requetes.reference` affiche sans ecriture les
heures de reference et les pointages de l'import initial pour le jour choisi.
Les decisions conservent leur nature, le motif, l'utilisateur et les heures,
montants et sanctions avant/apres. Les ordres archivent ces informations dans
leurs details JSON, repris sur le bulletin meme si la source change ensuite.

Dans la liste Imports et dossiers disponibles de la saisie manuelle, le
bouton Supprimer le dossier demande confirmation et ne concerne que les
requetes. La suppression retire le dossier et ses propres heures, sans
toucher l'import initial ni les autres dossiers. Des qu'un ordre de virement
est valide pour ce dossier, le bouton est desactive et le serveur refuse
egalement sa suppression, meme par appel direct a la route de suppression.
Le vidage de ses heures consolidees est egalement refuse apres validation.
