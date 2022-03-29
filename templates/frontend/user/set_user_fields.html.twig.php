{% if errors %}
<div class="errors">
    <div class="ui orange message">
        <div class="content">
            <div class="header">
                {% trans %}מספר נתונים חסרים. יש למלא את כל הנתונים בשדות המסומנים.{% endtrans %}
            </div>
            <p>
                {{ form_errors(form.username) }}
                {{ form_errors(form.email.first) }}
            </p>
        </div>
    </div>
</div>
{% endif %}

<div class="inner_form_bot">
    <div class="main_form {% if app.user.gender.id == 2 %} user_zigzug {% endif %}">

        <div class="mfrm_field cf">
            {{ form_label(form.region) }}
            <div class="mselect3">
                {{ form_widget(form.region) }}
            </div>
        </div>
        <div class="mfrm_field cf">
            {{ form_label(form.city) }}
            <div class="mselect3">
                {{ form_widget(form.city) }}
            </div>
        </div>
        {#<div class="mfrm_field cf">
            {{ form_label(form.area) }}
            <div class="mselect3">
                {{ form_widget(form.area) }}
            </div>
        </div>#}

    </div>
</div>










