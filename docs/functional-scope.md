# CampaignPlanner — Périmètre fonctionnel

# Objectif

CampaignPlanner est une application web permettant aux groupes de jeu de rôle de planifier facilement leurs prochaines sessions.

L'application est spécialisée dans la gestion collaborative des disponibilités des joueurs.

Elle n'a pas vocation à devenir un outil d'assistance au Maître du Jeu ni un gestionnaire de campagne.

Le projet doit rester volontairement simple, propre et facilement maintenable.

---

# Acteurs

## Maître du Jeu (Game Master)

Le Maître du Jeu possède un compte utilisateur authentifié.

Il peut :

* créer plusieurs campagnes ;
* gérer les participants de chacune de ses campagnes ;
* consulter les disponibilités de tous les participants ;
* ouvrir progressivement le calendrier sur les semaines à venir ;
* bloquer certaines dates ou périodes (vacances, indisponibilités...) ;
* sélectionner une date pour la prochaine session ;
* notifier les participants lorsque la session est planifiée.

Chaque Maître du Jeu ne peut accéder qu'à ses propres campagnes.

---

## Participant

Le participant ne possède pas de compte utilisateur.

Il accède à l'application grâce à une URL privée contenant un token unique permettant de l'identifier.

Il peut :

* consulter le calendrier de sa campagne ;
* renseigner ses disponibilités ;
* modifier ses réponses tant que le créneau est ouvert ;
* consulter en temps réel les disponibilités des autres participants.

Le participant ne peut accéder qu'à sa propre campagne.

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

* les participants ;
* le calendrier de disponibilités ;
* les futures sessions.

---

## Participant

Représente la participation d'une personne à une campagne.

Il possède notamment :

* un nom ou pseudo ;
* une adresse email ;
* éventuellement un numéro de téléphone ;
* éventuellement un nom de personnage ;
* un token privé.

Le participant ne possède pas de compte utilisateur.

Une même personne peut participer à plusieurs campagnes.

Chaque campagne possède son propre participant, même si plusieurs campagnes utilisent la même adresse email.

---

## CalendarSlot

Représente un créneau du calendrier.

Un créneau correspond à une date et éventuellement à une période de la journée ou une plage horaire.

Exemples :

* vendredi 14 août
* samedi 15 août (après-midi)
* samedi 15 août (soirée)

Un créneau peut être :

* ouvert ;
* bloqué ;
* sélectionné pour une session.

---

## Availability

Représente la disponibilité d'un participant pour un créneau.

Valeurs possibles :

* Disponible
* Peut-être
* Indisponible

Chaque participant possède une unique réponse par créneau.

Cette réponse peut être modifiée tant que le créneau reste ouvert.

---

## Session

Représente une session réellement planifiée.

Une session est créée lorsqu'un Maître du Jeu sélectionne un créneau du calendrier.

Elle servira notamment aux notifications et à l'historique des parties.

---

# Règles métier

## Utilisateurs

* Un Maître du Jeu peut posséder plusieurs campagnes.
* Une campagne appartient à un seul Maître du Jeu.
* Un Maître du Jeu ne peut jamais accéder aux campagnes d'un autre Maître du Jeu.

---

## Participants

* Une campagne contient plusieurs participants.
* Un participant représente la participation d'une personne à une campagne.
* Une même personne peut participer à plusieurs campagnes.
* Une même adresse email peut donc exister dans plusieurs campagnes.
* Une adresse email ne peut apparaître qu'une seule fois dans une même campagne.
* Chaque participant possède un token privé unique.
* Le token permet d'accéder directement à son interface sans authentification.
* Le participant peut être associé à un nom de personnage, purement informatif.

---

## Calendrier

* Chaque campagne possède un calendrier de disponibilités permanent.
* Le calendrier est composé de créneaux.
* Les joueurs peuvent renseigner leurs disponibilités sur tous les créneaux ouverts.
* Les disponibilités des autres participants sont visibles en temps réel.
* Un participant peut modifier sa réponse tant que le créneau reste ouvert.

---

## Blocage des créneaux

Le Maître du Jeu peut bloquer un ou plusieurs créneaux.

Un créneau bloqué :

* n'accepte plus de nouvelles réponses ;
* apparaît comme indisponible pour tous les participants.

Le blocage pourra éventuellement comporter un motif.

Exemples :

* Vacances
* Convention
* Absence

---

## Planification d'une session

À tout moment, le Maître du Jeu peut sélectionner un créneau afin d'en faire la prochaine session.

Cette action :

* crée une Session ;
* verrouille le créneau sélectionné ;
* déclenche l'envoi des notifications aux participants.

Le calendrier reste ensuite disponible pour préparer les sessions suivantes.

---

# Principes de conception

* Le modèle métier prime toujours sur le modèle technique.
* Une fonctionnalité n'est développée que si elle répond à un besoin métier identifié.
* Le MVP doit toujours rester fonctionnel.
* Les décisions d'architecture privilégient la simplicité, la lisibilité et l'évolutivité.
* CampaignPlanner doit rester un outil spécialisé dans la planification des sessions de jeu de rôle.

---

# Hors périmètre

CampaignPlanner ne gère pas :

* les fiches de personnages ;
* les PNJ ;
* les cartes ;
* les notes de campagne ;
* les combats ;
* les règles des jeux de rôle ;
* les outils d'assistance au Maître du Jeu.

Ces fonctionnalités pourront faire l'objet d'un projet indépendant.
