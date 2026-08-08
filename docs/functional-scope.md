# CampaignPlanner — Périmètre fonctionnel

# Objectif

CampaignPlanner est une application web permettant aux groupes de jeu de rôle de planifier facilement leurs prochaines sessions.

L'application est spécialisée dans la gestion collaborative des disponibilités des joueurs et dans la planification des sessions.

Elle n'a pas vocation à devenir un outil d'assistance au Maître du Jeu ni un gestionnaire de campagne.

Le projet doit rester volontairement simple, propre et facilement maintenable.

---

# Acteurs

## Maître du Jeu (Game Master)

Le Maître du Jeu possède un compte utilisateur authentifié.

Il peut :

- créer plusieurs campagnes ;
- modifier et archiver ses campagnes ;
- gérer les participants de chacune de ses campagnes ;
- consulter les disponibilités de tous les participants ;
- ouvrir progressivement le calendrier sur les semaines à venir ;
- bloquer certaines dates ou périodes ;
- sélectionner une date pour une prochaine session ;
- annuler une session planifiée ;
- consulter les sessions à venir et l'historique des sessions terminées.

Chaque Maître du Jeu ne peut accéder qu'à ses propres campagnes.

Une campagne archivée reste consultable par son Maître du Jeu mais ne peut plus être modifiée.

---

## Participant

Le participant ne possède pas de compte utilisateur.

Il accède à l'application grâce à des URL privées contenant des tokens uniques.

Deux types d'accès existent :

- un accès à son espace joueur ;
- un accès au calendrier d'une participation précise.

L'espace joueur permet de regrouper les différentes campagnes auxquelles une même personne participe.

Le participant peut :

- consulter les campagnes actives auxquelles il participe ;
- consulter ses prochaines sessions ;
- accéder au calendrier de chacune de ses campagnes ;
- renseigner ses disponibilités ;
- modifier ses réponses tant que le créneau est ouvert ;
- consulter les disponibilités des autres participants.

Un participant ne peut accéder qu'aux campagnes auxquelles il participe.

Les campagnes archivées ne sont plus affichées dans son espace joueur et leur calendrier n'est plus accessible.

---

# Concepts métier

## User

Représente un Maître du Jeu.

Un utilisateur authentifié peut posséder plusieurs campagnes.

---

## Campaign

Représente une campagne de jeu de rôle.

Une campagne appartient à un seul Maître du Jeu.

Elle contient :

- les participants ;
- le calendrier de disponibilités ;
- les sessions planifiées ;
- l'historique des sessions.

Une campagne peut être active ou archivée.

Une campagne ne peut pas être archivée tant qu'elle possède encore une session future planifiée.

Une campagne archivée :

- reste consultable par son propriétaire ;
- ne peut plus être modifiée ;
- ne permet plus l'accès à son calendrier ;
- n'apparaît plus dans l'espace joueur des participants.

---

## Participant

Représente la participation d'une personne à une campagne.

Il possède notamment :

- un nom ou pseudo ;
- une adresse email ;
- éventuellement un numéro de téléphone ;
- éventuellement un nom de personnage ;
- un token d'accès au calendrier ;
- un token permettant l'accès à l'espace joueur.

Le participant ne possède pas de compte utilisateur.

Une même personne peut participer à plusieurs campagnes.

Chaque campagne possède son propre Participant, même si plusieurs campagnes utilisent la même adresse email.

Les participations utilisant une même adresse email peuvent être regroupées dans un même espace joueur.

Un participant peut être archivé.

---

## CalendarSlot

Représente un créneau du calendrier.

Un créneau correspond à une date et à une période de la journée.

Les périodes actuellement utilisées sont notamment :

- après-midi ;
- soirée.

Un créneau peut être :

- ouvert ;
- bloqué ;
- sélectionné pour une session.

---

## Availability

Représente la disponibilité d'un participant pour un créneau.

Valeurs possibles :

- Disponible ;
- Peut-être ;
- Indisponible.

Chaque participant possède une unique réponse par créneau.

Cette réponse peut être modifiée tant que le créneau reste ouvert et que la période concernée n'est pas passée.

---

## GameSession

Représente une session réellement planifiée.

Une session est créée lorsqu'un Maître du Jeu sélectionne un créneau du calendrier.

Une session possède un état.

États actuellement utilisés :

- `scheduled` : session planifiée ;
- `completed` : session passée et terminée ;
- `cancelled` : session annulée.

Les sessions permettent :

- l'envoi des notifications de planification ;
- l'envoi des rappels ;
- l'annulation ;
- l'affichage des prochaines sessions ;
- la constitution de l'historique d'une campagne.

---

# Règles métier

## Utilisateurs

- Un Maître du Jeu peut posséder plusieurs campagnes.
- Une campagne appartient à un seul Maître du Jeu.
- Un Maître du Jeu ne peut jamais modifier une campagne appartenant à un autre utilisateur.
- Les contrôles d'accès sont appliqués côté serveur.

---

## Participants

- Une campagne contient plusieurs participants.
- Un participant représente la participation d'une personne à une campagne.
- Une même personne peut participer à plusieurs campagnes.
- Une même adresse email peut donc exister dans plusieurs campagnes.
- Une adresse email ne peut apparaître qu'une seule fois dans une même campagne.
- Chaque participation possède un token privé permettant l'accès à son calendrier.
- Les participations associées à une même adresse email peuvent partager un accès commun à l'espace joueur.
- Le participant peut être associé à un nom de personnage, purement informatif.
- Un participant archivé n'est plus considéré comme un participant actif.

---

## Espace joueur

L'espace joueur regroupe les participations actives correspondant à une même personne.

Il permet notamment de consulter :

- les campagnes actives ;
- la prochaine session ;
- les autres sessions à venir ;
- les accès aux calendriers correspondants.

Les campagnes archivées sont exclues de cet espace.

---

## Calendrier

Chaque campagne active possède un calendrier de disponibilités permanent.

Le calendrier est composé de créneaux.

Le Maître du Jeu peut naviguer dans les semaines et ouvrir progressivement de nouvelles périodes.

Les participants peuvent renseigner leurs disponibilités sur les créneaux ouverts.

Les disponibilités du groupe sont visibles depuis les interfaces prévues à cet effet.

Un participant peut modifier sa réponse tant que :

- le créneau reste ouvert ;
- la période concernée n'est pas passée ;
- la campagne reste active.

Le calendrier d'une campagne archivée n'est plus accessible.

---

## Blocage des créneaux

Le Maître du Jeu peut bloquer un ou plusieurs créneaux.

Un créneau bloqué :

- n'accepte plus de nouvelles réponses ;
- ne peut plus être modifié par les participants ;
- apparaît comme indisponible pour la planification.

Aucun motif de blocage n'est requis.

---

## Planification d'une session

Le Maître du Jeu peut sélectionner un créneau disponible afin d'en faire une session.

Cette action :

- crée une GameSession ;
- place la session dans l'état `scheduled` ;
- place le créneau dans l'état `selected` ;
- verrouille le créneau sélectionné ;
- déclenche l'envoi d'une notification aux participants.

Le calendrier reste ensuite disponible afin de préparer les sessions suivantes.

---

## Annulation d'une session

Une session planifiée peut être annulée par le Maître du Jeu.

Cette action :

- passe la session dans l'état `cancelled` ;
- transforme le créneau correspondant en créneau bloqué ;
- retire la session de la liste des sessions à venir ;
- déclenche l'envoi d'une notification d'annulation aux participants.

La session annulée reste conservée en base afin de préserver son historique.

---

## Sessions terminées

Une session planifiée dont la date est passée est automatiquement placée dans l'état `completed`.

Le traitement est réalisé automatiquement par une tâche planifiée.

Une session du jour n'est pas considérée comme terminée avant le jour suivant.

Les sessions terminées constituent l'historique de la campagne.

---

## Rappels automatiques

Une notification de rappel est envoyée avant une session planifiée.

Le rappel est normalement envoyé sept jours avant la date de la session.

Afin de résister à une interruption temporaire du système, une session peut également être rappelée plus tard si :

- elle a lieu dans sept jours ou moins ;
- elle n'a pas encore eu lieu ;
- aucun rappel n'a encore été envoyé ;
- elle est toujours dans l'état `scheduled`.

Chaque session ne peut recevoir qu'un seul rappel automatique.

La date d'envoi du rappel est enregistrée afin d'éviter les doublons.

---

## Archivage d'une campagne

Une campagne peut être archivée lorsqu'elle est terminée.

L'archivage est impossible si la campagne possède encore une session future planifiée.

Après archivage :

- la campagne reste consultable par son Maître du Jeu ;
- les actions de modification ne sont plus autorisées ;
- son calendrier n'est plus accessible ;
- les participants ne la voient plus dans leur espace joueur.

La réouverture d'une campagne archivée ne fait actuellement pas partie du périmètre.

---

# Notifications

CampaignPlanner envoie actuellement des notifications par email lors des événements suivants :

- planification d'une nouvelle session ;
- annulation d'une session ;
- rappel d'une session à venir.

L'envoi peut être traité de manière asynchrone selon l'environnement.

---

# Automatisations

CampaignPlanner possède un traitement périodique chargé :

- d'envoyer les rappels de sessions à venir ;
- de passer automatiquement les sessions passées dans l'état `completed`.

Ce traitement est exécuté quotidiennement par un scheduler dédié.

La logique métier reste indépendante du mécanisme utilisé pour déclencher cette tâche afin de permettre son adaptation à l'environnement de production.

---

# Principes de conception

- Le modèle métier prime toujours sur le modèle technique.
- Une fonctionnalité n'est développée que si elle répond à un besoin métier identifié.
- Le MVP doit toujours rester fonctionnel.
- Les décisions d'architecture privilégient la simplicité, la lisibilité et l'évolutivité.
- Les contrôles importants sont appliqués côté serveur et ne reposent pas uniquement sur l'interface.
- CampaignPlanner doit rester un outil spécialisé dans la planification des sessions de jeu de rôle.

---

# Hors périmètre

CampaignPlanner ne gère pas :

- les fiches de personnages ;
- les PNJ ;
- les cartes ;
- les notes de campagne ;
- les combats ;
- les règles des jeux de rôle ;
- les outils d'assistance au Maître du Jeu ;
- le déroulement ou le contenu d'une session de jeu.

Ces fonctionnalités pourront faire l'objet d'un projet indépendant.

---

# État du MVP

Le périmètre fonctionnel principal du MVP est implémenté.

Avant la mise en production, les travaux restants concernent principalement :

- la vérification visuelle et responsive de l'ensemble des pages ;
- la validation des principaux parcours utilisateur ;
- la configuration de l'environnement de production ;
- la configuration du transport email de production ;
- la mise en place du scheduler dans l'environnement de production ;
- la préparation et l'import des données initiales ;
- la configuration des secrets, du domaine et du HTTPS.

Aucune fonctionnalité métier majeure supplémentaire n'est nécessaire pour considérer le MVP comme utilisable.
