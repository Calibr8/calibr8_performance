
Installation
------------

Your html.tpl.php should look like this.
This is present in the latest version of the calibr8_theme. If you installed this module on an old calibr8 setup or
a site without the calibr8 themes, make sure these variables are printed.

<html>
  <head>
    ...
    <title>
    {{ resource_hints }}
    ...
    {% if place_scripts_in_head %}
      <js-bottom-placeholder token="{{ placeholder_token|raw }}">
    {% endif %}
  </head>
  <body>
    ...
    {% if not place_scripts_in_head %}
      <js-bottom-placeholder token="{{ placeholder_token|raw }}">
    {% endif %}
  </body>
</html>