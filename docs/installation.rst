Installation
------------

Using `Composer <http://getcomposer.org/>`__
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code:: bash

	composer require pieceofcake2/upload

This plugin has the type ``cakephp-plugin`` set in its own
``composer.json`` and is installed by ``composer/installers``. Your
application's ``composer.json`` must allow the ``composer/installers``
plugin and set ``installer-paths``, otherwise the plugin is not installed
into a directory where ``CakePlugin::load()`` can find it:

.. code:: json

	{
		"config": {
			"allow-plugins": {
				"composer/installers": true
			}
		},
		"extra": {
			"installer-paths": {
				"plugins/{$name}/": ["type:cakephp-plugin"]
			}
		}
	}

For the classic ``app/`` layout use ``"app/Plugin/{$name}/"`` instead.
It is recommended that you add ``/plugins/Upload`` to your .gitignore file.
(Why? `read
this <http://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md>`__.)

Manual
~~~~~~

-  Download this:
   https://github.com/pieceofcake2/upload/archive/2.x.zip
-  Unzip that download.
-  Copy the resulting folder to ``plugins`` (or ``app/Plugin``)
-  Rename the folder you just copied to ``Upload``

GIT Clone
~~~~~~~~~

In your ``plugins`` directory type:

.. code:: bash

	git clone -b 2.x https://github.com/pieceofcake2/upload.git Upload

Imagick Support
---------------

To enable `Imagick <http://www.imagemagick.org/>`__ support, you need to
have Imagick installed:

.. code:: bash

	# Debian systems
	sudo apt-get install php-imagick

	# From pecl
	pecl install imagick

If you cannot install Imagick, instead configure the plugin with
``'thumbnailMethod'  => 'php'`` in the files options.

Enable plugin
-------------

You need to enable the plugin in your ``config/bootstrap.php`` (or ``app/Config/bootstrap.php``) file:

.. code:: php

	<?php
	CakePlugin::load('Upload');

If you are already using ``CakePlugin::loadAll();``, then this is not
necessary.
