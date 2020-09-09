INTRODUCTION 
------------

You should never modify a theme that is packaged and released from Drupal.org 
because, if you do, all your changes will be lost once that theme is updated.
Instead, you should create a subtheme from one of the starterkits folders. 
Once you've done that, you can override styles, templates...


INSTRUCTIONS FOR CREATING A SUBTHEME
------------------------------------

1.- Download da_vinci: composer require drupal/da_vinci

2.- Enable da_vinci theme: drush en da_vinci -y

2.- Create a sub-theme: drush --include="themes/contrib/da_vinci" subtheme NAME
    Optional options:
    - machine-name: The machine-readable name of your sub-theme. This will be
      auto-generated from the human-readable name if omitted.
    - description: The description of your sub-theme.
    - destination: The destination of your sub-theme.
    - starterkit: The name of the starter kit to use. By default it is default
      folder.

3.- Set default theme:
    - drush en NAME -y; drush config-set system.theme default NAME -y
    or
    - Go to admin/appearance and click the "Enable and set default" option.

4.- Install dependencies: cd /path/to/subtheme and run npm install

Now you can override everything you need in your subtheme. If you need some
file, you can took from Da Vinci original theme.

Don't forget read the Da Vinci README.md for know more about installation,
files and usage.
