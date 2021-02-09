<?php

namespace OCA\Libresign\Service;

use OCP\IL10N;

class WebhookService {
	/** @var IL10N */
	private $l10n;

	public function __construct(
		IL10N $l10n
	) {
		$this->l10n = $l10n;
	}

	public function validate(array $data) {
		$this->validateFile($data);
		$this->validateUsers($data);
	}

	public function validateFile($data) {
		if (empty($data['file'])) {
			throw new \Exception((string)$this->l10n->t('Empty file'));
		}
		if (empty($data['file']['url']) && empty($data['file']['base64'])) {
			throw new \Exception((string)$this->l10n->t('Inform url or base64 to sign'));
		}
		if (!empty($data['file']['url'])) {
			if (!filter_var($data['file']['url'], FILTER_VALIDATE_URL)) {
				throw new \Exception((string)$this->l10n->t('Invalid url file'));
			}
		}
		if (!empty($data['file']['base64'])) {
			$input = base64_decode($data['file']['base64']);
			$base64 = base64_encode($input);
			if ($input != $base64) {
				throw new \Exception((string)$this->l10n->t('Invalid base64 file'));
			}
		}
	}

	public function validateUsers($data) {
		if (empty($data['users'])) {
			throw new \Exception((string)$this->l10n->t('Empty users collection'));
		}
		if (!is_array($data['users'])) {
			throw new \Exception((string)$this->l10n->t('User collection need is an array'));
		}
		foreach ($data['users'] as $index => $user) {
			$this->validateUser($user, $index);
		}
	}

	public function validateUser($user, $index) {
		if (!is_array($user)) {
			throw new \Exception((string)$this->l10n->t('User collection need is an array: user ' . $index));
		}
		if (!$user) {
			throw new \Exception((string)$this->l10n->t('User collection need is an array with values: user ' . $index));
		}
		if (empty($user['email'])) {
			throw new \Exception((string)$this->l10n->t('User need an email: user ' . $index));
		}
		if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
			throw new \Exception((string)$this->l10n->t('Invalid email: user ' . $index));
		}
	}
}
