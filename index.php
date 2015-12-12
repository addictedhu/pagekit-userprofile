<?php

use Bixie\Userprofile\Event\UserListener;

return [

	'name' => 'bixie/userprofile',

	'type' => 'extension',

	'main' => 'Bixie\\Userprofile\\UserprofileModule',

	'autoload' => [

		'Bixie\\Userprofile\\' => 'src'

	],

	'nodes' => [

	],

	'routes' => [

		'/profile' => [
			'name' => '@userprofile',
			'controller' => 'Bixie\\Userprofile\\Controller\\ProfileController'
		],
		'/userprofile' => [
			'name' => '@userprofile/admin',
			'controller' => [
				'Bixie\\Userprofile\\Controller\\UserprofileController',
				'Bixie\\Userprofile\\Controller\\FieldController'
			]
		],
		'/api/userprofile/field' => [
			'name' => '@site/api/field',
			'controller' => 'Bixie\\Userprofile\\Controller\\FieldApiController'
		],
		'/api/userprofile/profile' => [
			'name' => '@site/api/profile',
			'controller' => 'Bixie\\Userprofile\\Controller\\ProfileApiController'
		]

	],

	'userprofilefields' => 'fieldtypes',

	'resources' => [

		'bixie/userprofile:' => ''

	],

	'menu' => [

		'userprofile' => [
			'label' => 'Userprofile',
			'icon' => 'packages/bixie/userprofile/icon.svg',
			'url' => '@userprofile/admin',
			// 'access' => 'userprofile: manage hellos',
			'active' => '@userprofile(/*)'
		],

		'userprofile: fields' => [
			'label' => 'Fields',
			'parent' => 'userprofile',
			'url' => '@userprofile/admin',
			// 'access' => 'userprofile: manage hellos',
			'active' => '@userprofile(/edit)?'
		]

	],

	'permissions' => [

		'userprofile: manage settings' => [
			'title' => 'Manage settings'
		],

	],

	'settings' => 'settings-userprofile',

	'config' => [

		'override_registration' => 1

	],

	'events' => [

		'boot' => function ($event, $app) {
			$app->subscribe(new UserListener);
		},

		'request' => function ($event, $request) use ($app) {
			if ($app->config('bixie/userprofile')->get('override_registration', true) && $request->attributes->get('_route') == '@user/registration') {
				$event->setResponse($app->redirect('@userprofile/registration'), [], 301);
			}
		},

		'view.scripts' => function ($event, $scripts) use ($app) {
			$scripts->register('userprofile-settings', 'bixie/userprofile:app/bundle/settings.js', '~extensions');
			$scripts->register('link-userprofile', 'bixie/userprofile:app/bundle/link-userprofile.js', '~panel-link');
			$scripts->register('user-section-userprofile', 'bixie/userprofile:app/bundle/user-section-userprofile.js', ['~user-edit', 'userprofile-profilefields']);
			//register fields
			$scripts->register('userprofile-profilefieldmixin', 'bixie/userprofile:app/bundle/userprofile-profilefieldmixin.js', 'vue');
			$scripts->register('userprofile-profilefields', 'bixie/userprofile:app/bundle/userprofile-profilefields.js', ['vue', 'userprofile-profilefieldmixin']);
			$userprofile = $app->module('bixie/userprofile');
			foreach ($userprofile->getTypes() as $type) {
				$scripts->register(
					'userprofile-' . $type['id'], 'bixie/userprofile:app/bundle/userprofile-' . $type['id'] . '.js',
					array_merge(['~userprofile-profilefields'], $type['dependancies'])
				);
			}
		},

		'view.styles' => function ($event, $styles) use ($app) {
			//todo this should be prettier
			$route = $app->request()->attributes->get('_route');
			if (strpos($route, '@userprofile') === 0 || in_array($route, ['@user/edit'])) {
				$userprofile = $app->module('bixie/userprofile');
				foreach ($userprofile->getTypes() as $type) {
					if (isset($type['style'])) {
						foreach ($type['style'] as $name => $source) {
							$styles->add($name, $source);

						}
					}
				}
			}
		},

		'console.init' => function ($event, $console) {

			$console->add(new Bixie\Userprofile\Console\Commands\TranslateCommand());

		}

	]

];
