{% extends 'public_base.html.twig' %}

{% block title %}
    Mes disponibilités — {{ campaign.name }}
{% endblock %}

{% block body %}
    {% include 'components/page-header.html.twig' with {
        eyebrow: campaign.name,
        title: 'Bonjour ' ~ participant.name,
        description: 'Indiquez vos disponibilités pour la semaine du '
            ~ calendar.start|date('d/m/Y')
            ~ ' au '
            ~ calendar.end|date('d/m/Y')
            ~ '. Les réponses des autres participants sont visibles.'
    } %}

    <section class="calendar-toolbar">
        <a
            class="button button--secondary"
            href="{{ path('participant_availability_show', {
                token: participant.accessToken,
                week: calendar.previousWeek|date('Y-m-d')
            }) }}"
        >
            ← Semaine précédente
        </a>

        <a
            class="button button--secondary"
            href="{{ path('participant_availability_show', {
                token: participant.accessToken
            }) }}"
        >
            Cette semaine
        </a>

        <a
            class="button button--secondary"
            href="{{ path('participant_availability_show', {
                token: participant.accessToken,
                week: calendar.nextWeek|date('Y-m-d')
            }) }}"
        >
            Semaine suivante →
        </a>
    </section>

    <form
        {% if not isPastWeek %}
            method="post"
            action="{{ path('participant_availability_save', {
                token: participant.accessToken
            }) }}"
            data-controller="availability-grid"
            data-action="submit->availability-grid#submit"
        {% endif %}
    >
        {% if not isPastWeek %}
            <input
                type="hidden"
                name="_token"
                value="{{ csrf_token(
                    'save-availabilities-' ~ participant.id
                ) }}"
            >

            <input
                type="hidden"
                name="week"
                value="{{ calendar.start|date('Y-m-d') }}"
            >

            <section class="availability-editor">
                <div class="availability-editor__status">
                    <span class="availability-editor__label">
                        Statut à appliquer
                    </span>

                    <button
                        type="button"
                        class="availability-status-button is-available"
                        data-availability-grid-target="statusButton"
                        data-action="availability-grid#selectStatus"
                        data-status="available"
                    >
                        ✓ Disponible
                    </button>

                    <button
                        type="button"
                        class="availability-status-button is-maybe"
                        data-availability-grid-target="statusButton"
                        data-action="availability-grid#selectStatus"
                        data-status="maybe"
                    >
                        ? Peut-être
                    </button>

                    <button
                        type="button"
                        class="availability-status-button is-unavailable"
                        data-availability-grid-target="statusButton"
                        data-action="availability-grid#selectStatus"
                        data-status="unavailable"
                    >
                        × Indisponible
                    </button>

                    <button
                        type="button"
                        class="availability-status-button is-empty"
                        data-availability-grid-target="statusButton"
                        data-action="availability-grid#selectStatus"
                        data-status=""
                    >
                        — Effacer
                    </button>
                </div>

                <div class="availability-editor__shortcuts">
                    <span class="availability-editor__label">
                        Actions rapides
                    </span>

                    <button
                        type="button"
                        class="button button--secondary"
                        data-action="availability-grid#applyToAll"
                        data-scope="all"
                    >
                        Toute la semaine
                    </button>

                    <button
                        type="button"
                        class="button button--secondary"
                        data-action="availability-grid#applyToAll"
                        data-scope="afternoon"
                    >
                        Tous les après-midi
                    </button>

                    <button
                        type="button"
                        class="button button--secondary"
                        data-action="availability-grid#applyToAll"
                        data-scope="evening"
                    >
                        Tous les soirs
                    </button>
                </div>
            </section>
        {% else %}
            <div class="calendar-readonly-notice">
                Cette semaine est passée et ne peut plus être modifiée.
            </div>
        {% endif %}

        <section class="calendar-legend">
            <span class="calendar-legend__item is-available">
                <span class="calendar-legend__symbol">✓</span>
                Disponible
            </span>

            <span class="calendar-legend__item is-maybe">
                <span class="calendar-legend__symbol">?</span>
                Peut-être
            </span>

            <span class="calendar-legend__item is-unavailable">
                <span class="calendar-legend__symbol">×</span>
                Indisponible
            </span>

            <span class="calendar-legend__item is-empty">
                <span class="calendar-legend__symbol">—</span>
                Non renseigné
            </span>

            <span class="calendar-legend__item is-blocked">
                <span class="calendar-legend__symbol">×</span>
                Créneau bloqué
            </span>
        </section>

        <section class="calendar-card">
            <div class="availability-table">
                <div class="availability-table__corner">
                    Participant
                </div>

                {% for day in calendar.days %}
                    <div class="availability-table__day">
                        <strong>{{ day.label }}</strong>
                        <span>{{ day.date|date('d/m') }}</span>
                    </div>
                {% endfor %}

                <div class="availability-table__subheader"></div>

                {% for day in calendar.days %}
                    <div class="availability-table__periods">
                        <span>Après-midi</span>
                        <span>Soir</span>
                    </div>
                {% endfor %}

                {% for row in calendar.rows %}
                    {% set isCurrentParticipant =
                        row.participant.id == participant.id
                    %}

                    {% set isEditable =
                        isCurrentParticipant and not isPastWeek
                    %}

                    <div
                        class="
                            availability-table__participant
                            {{ isCurrentParticipant
                                ? 'is-current-participant'
                            }}
                        "
                    >
                        <strong>
                            {% if isCurrentParticipant %}
                                Vous —
                            {% endif %}

                            {{ row.participant.name }}
                        </strong>

                        {% if row.participant.characterName %}
                            <span>{{ row.participant.characterName }}</span>
                        {% endif %}
                    </div>

                    {% for dayIndex in 0..6 %}
                        {% set afternoonCell = row.cells[dayIndex * 2] %}
                        {% set eveningCell = row.cells[(dayIndex * 2) + 1] %}

                        <div
                            class="
                                availability-table__cells
                                {{ isCurrentParticipant
                                    ? 'is-current-participant'
                                }}
                            "
                        >
                            <div
                                class="
                                    availability-cell
                                    {{ afternoonCell.cssClass }}
                                    {{ isEditable ? 'is-editable' }}
                                "
                                title="{{ afternoonCell.label }}"

                                {% if isEditable %}
                                    data-availability-grid-target="cell"
                                    data-action="click->availability-grid#updateCell"
                                    data-slot-id="{{ afternoonCell.slot.id }}"
                                    data-period="afternoon"
                                    data-status="{{
                                        afternoonCell.status
                                            ? afternoonCell.status.value
                                            : ''
                                    }}"
                                    data-blocked="{{
                                        afternoonCell.blocked
                                            ? 'true'
                                            : 'false'
                                    }}"
                                {% endif %}
                            >
                                <span class="availability-cell__symbol">
                                    {{ afternoonCell.symbol }}
                                </span>
                            </div>

                            {% if isEditable %}
                                <input
                                    type="hidden"
                                    name="availabilities[{{ afternoonCell.slot.id }}]"
                                    value="{{
                                        afternoonCell.status
                                            ? afternoonCell.status.value
                                            : ''
                                    }}"
                                    data-availability-grid-target="input"
                                    data-slot-id="{{ afternoonCell.slot.id }}"
                                    data-initial-value="{{
                                        afternoonCell.status
                                            ? afternoonCell.status.value
                                            : ''
                                    }}"
                                >
                            {% endif %}

                            <div
                                class="
                                    availability-cell
                                    {{ eveningCell.cssClass }}
                                    {{ isEditable ? 'is-editable' }}
                                "
                                title="{{ eveningCell.label }}"

                                {% if isEditable %}
                                    data-availability-grid-target="cell"
                                    data-action="click->availability-grid#updateCell"
                                    data-slot-id="{{ eveningCell.slot.id }}"
                                    data-period="evening"
                                    data-status="{{
                                        eveningCell.status
                                            ? eveningCell.status.value
                                            : ''
                                    }}"
                                    data-blocked="{{
                                        eveningCell.blocked
                                            ? 'true'
                                            : 'false'
                                    }}"
                                {% endif %}
                            >
                                <span class="availability-cell__symbol">
                                    {{ eveningCell.symbol }}
                                </span>
                            </div>

                            {% if isEditable %}
                                <input
                                    type="hidden"
                                    name="availabilities[{{ eveningCell.slot.id }}]"
                                    value="{{
                                        eveningCell.status
                                            ? eveningCell.status.value
                                            : ''
                                    }}"
                                    data-availability-grid-target="input"
                                    data-slot-id="{{ eveningCell.slot.id }}"
                                    data-initial-value="{{
                                        eveningCell.status
                                            ? eveningCell.status.value
                                            : ''
                                    }}"
                                >
                            {% endif %}
                        </div>
                    {% endfor %}
                {% else %}
                    <div class="availability-table__empty">
                        Aucun participant actif dans cette campagne.
                    </div>
                {% endfor %}

                {% if calendar.rows is not empty %}
                    <div class="availability-table__results-label">
                        Résultats
                    </div>

                    {% for day in calendar.days %}
                        <div class="availability-table__results">
                            <div class="availability-table__result-slot">
                                {% if day.afternoonSlot.blocked %}
                                    <span class="availability-table__result-blocked">
                                        Créneau bloqué
                                    </span>
                                {% else %}
                                    <span class="availability-table__summary-item is-available">
                                        {{ day.afternoonSummary.availableCount }}
                                        {{ day.afternoonSummary.availableCount > 1
                                            ? 'disponibles'
                                            : 'disponible'
                                        }}
                                    </span>

                                    <span class="availability-table__summary-item is-maybe">
                                        {{ day.afternoonSummary.maybeCount }}
                                        peut-être
                                    </span>

                                    <span class="availability-table__summary-item is-unavailable">
                                        {{ day.afternoonSummary.unavailableCount }}
                                        {{ day.afternoonSummary.unavailableCount > 1
                                            ? 'indisponibles'
                                            : 'indisponible'
                                        }}
                                    </span>

                                    <span class="availability-table__summary-item is-unanswered">
                                        {{ day.afternoonSummary.unansweredCount }}
                                        sans réponse
                                    </span>
                                {% endif %}
                            </div>

                            <div class="availability-table__result-slot">
                                {% if day.eveningSlot.blocked %}
                                    <span class="availability-table__result-blocked">
                                        Créneau bloqué
                                    </span>
                                {% else %}
                                    <span class="availability-table__summary-item is-available">
                                        {{ day.eveningSummary.availableCount }}
                                        {{ day.eveningSummary.availableCount > 1
                                            ? 'disponibles'
                                            : 'disponible'
                                        }}
                                    </span>

                                    <span class="availability-table__summary-item is-maybe">
                                        {{ day.eveningSummary.maybeCount }}
                                        peut-être
                                    </span>

                                    <span class="availability-table__summary-item is-unavailable">
                                        {{ day.eveningSummary.unavailableCount }}
                                        {{ day.eveningSummary.unavailableCount > 1
                                            ? 'indisponibles'
                                            : 'indisponible'
                                        }}
                                    </span>

                                    <span class="availability-table__summary-item is-unanswered">
                                        {{ day.eveningSummary.unansweredCount }}
                                        sans réponse
                                    </span>
                                {% endif %}
                            </div>
                        </div>
                    {% endfor %}
                {% endif %}
            </div>
        </section>

        {% if not isPastWeek %}
            <div class="availability-save">
                <p class="availability-save__hint">
                    Les modifications ne sont enregistrées qu’après validation.
                </p>

                <button
                    type="submit"
                    class="button button--primary"
                    data-availability-grid-target="saveButton"
                    disabled
                >
                    Enregistrer mes disponibilités
                </button>
            </div>
        {% endif %}
    </form>
{% endblock %}