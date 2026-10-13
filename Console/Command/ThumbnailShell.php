<?php
App::uses('AppShell', 'Console/Command');
App::uses('ConnectionManager', 'Model');
App::uses('Model', 'Model');

/**
 * Thumbnail Shell
 * This shell can be used to generate new thumbnail images when model settings
 * for the thumbnail sizes changes.
 *
 * @author David Yell <dyell@ukwebmedia.com>
 */
class ThumbnailShell extends AppShell {

/**
 * Return the shell options
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addSubcommand('generate', ['help' => 'Find models and regenerate the thumbnails.']);
		return $parser;
	}

/**
 * Regenerate thumbnails for images linked in the database
 *
 * @return void
 */
	public function generate() {
		$modelName = $this->in(__d('upload', 'Which model would you like to regenerate thumbnails for?'));

		try {
			$this->loadModel($modelName);
		} catch (Exception $exc) {
			return $this->error($exc->getMessage());
		}

		[, $modelName] = pluginSplit($modelName);
		$Model = $this->{$modelName};

		if ($Model::class === 'AppModel') {
			return $this->error(__d('upload', "Model '%s' not found.", $modelName));
		}

		$behaviorName = null;
		foreach (['Upload', 'FileImport', 'FileGrabber'] as $name) {
			if ($Model->Behaviors->attached($name)) {
				$behaviorName = $name;
				break;
			}
		}

		if ($behaviorName === null) {
			return $this->error(__d('upload', "Model '%s' does not use the Upload behavior.", $modelName));
		}

		$Behavior = $Model->Behaviors->{$behaviorName};
		$behaviorConfig = $Behavior->settings[$Model->alias];

		foreach ($behaviorConfig as $field => $config) {
			$dirField = $config['fields']['dir'];

			// The source file is re-saved in place, so it must not be removed as an "old" file
			$Behavior->uploadSettings($Model, $field, 'deleteOnUpdate', false);

			$files = $Model->find('all', [
				'fields' => [$Model->primaryKey, $field, $dirField],
				'conditions' => [
					$Model->alias . '.' . $field . ' IS NOT NULL'
				],
				'recursive' => -1,
			]);

			foreach ($files as $file) {
				$record = $file[$Model->alias];
				$sourceFilePath = $config['path'] . $record[$dirField] . DS . $record[$field];

				if (!is_file($sourceFilePath)) {
					continue;
				}

				// $field needs to be an array like uploading an image
				$fieldData = [
					'name' => basename($sourceFilePath),
					'type' => mime_content_type($sourceFilePath),
					'size' => filesize($sourceFilePath),
					'tmp_name' => $sourceFilePath,
					'error' => UPLOAD_ERR_OK
				];

				$data = [
					$Model->primaryKey => $record[$Model->primaryKey],
					$field => $fieldData,
				];

				if ($Model->hasField('modified')) {
					$data['modified'] = false;
				}

				$Model->create();
				$Model->set($data);

				if ($Model->save(null, false)) {
					$this->out(__d('upload', 'Created thumbnails in %s for %s', $record[$dirField], $record[$field]));
				} else {
					$this->err(__d('upload', '<error>Could not create thumbnails in %s for %s</error>', $record[$dirField], $record[$field]));
				}
			}

			$Behavior->uploadSettings($Model, $field, 'deleteOnUpdate', $config['deleteOnUpdate']);
		}
	}
}
