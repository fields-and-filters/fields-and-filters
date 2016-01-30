Fields and Filters
====================

Fields and Filters is a manager to manage the additional fields as well as the ability to filter them. (elements).

Component does not overwrite the Joomla core files.

__Fields:__

- easy ability to manage fields
- 6 fields – input, image, textarea/editor, checkboxlist (checkboxlist field can be use to filtering), url, date
- flexible configuration
- add fields to different position of the article
- add values field without first saving article
- convection of editing article view with Fields and Fields, does not require the opening of the component in modal window
- default field templates

__Filters:__

- module to display fields type filters
- ajax filtering articles
- hash navigation. Showcase
- using the list view articles, without any additional CSS styles for the view filters
- default filter templates
- Random filters:
    - Random from all values - the module itself random filter values, and then get matching articles to values
    - Random from selected values - user himself selected values and then get random articles from selected values


__Simple Syntax `#{}`:__ You can insert Fields anywhere on your site by using Syntax:

- in the same article and component: `#{field_id}`
- in another article and the same component: `#{field_id,article_id}`
- in another component: `#{field_id,article_id:option(e.g. com_content)}`
- `#{field_id.{params}}`  - changing fields and filters parameters on the “fly” (format json), eg. `{'base' => {'show_name': 1}}`
- `#{field_id,context}` - in what context you need display the fields, eg.: mod_custom.content. The field will be displayed only in the custom module
- `#{field_id,article_id:option,context,{params}}` - Simple Sytax with all options
