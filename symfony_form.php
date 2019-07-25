<?php
// ---------------------------------------------

// Самый простой способ вывода формы
{{ form_start(form, {attr: {class: 'my-form-class'} }) }}
    {{ form_widget(form) }}
{{ form_end(form) }}

// ---------------------------------------------

// Шаблоны

/* Каждое поле состоит из 5 компонентов: строки и 4 вещи (widget, label, errors, help)
 * 
 * Каждое поле имеет тип. Чтобы вывести виджет для типа email, symfony ищет блок с названием email_widget (field-type_part)
 * Однако могут быть исключения, например, label, к-й не имеет отличий в зависимости от типа поля.
 * 
 * Блок email_widget может искаться в
 * bootstrap_4_layout.html.twig - используется, если указана в настройках config/packages/twig.yaml
 * form_div_layout.html.twig - используется по-умолчанию
 * 
 * В параметрах можно указывать сразу несколько тем, т.к. каждая из них может переопределять (redefine) часть элементов.
 * Порядок указания важен, каждая следующая тема может переопределять предыдущую.
 * 
 * */

// Применение темы только к конкретной форме осуществляется в шаблоне перед началом формы
{% form_theme bookForm 'foundation_5_layout.html.twig' %}
{{ form_start(bookForm) }} {{ form_end(bookForm) }}

// Несколько тем
{% form_theme bookForm with ['foundation_5_layout.html.twig', 'forms/my_custom_theme.html.twig'] %}
{{ form_start(bookForm) }} {{ form_end(bookForm) }}

// Редактирование конкретного поля формы
{% form_theme registrationForm _self %}

{% block _product_name_widget %}
    <div class="text_widget">
        {{ block('form_widget_simple') }}
    </div>
{% endblock %}

// ---------------------------------------------
