<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023 Vitor Mattos <vitor@php.rio>
 *
 * @author Vitor Mattos <vitor@php.rio>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Libresign\Controller;

use OC\AppFramework\Http as AppFrameworkHttp;
use OCA\Libresign\AppInfo\Application;
use OCA\Libresign\Db\File as FileEntity;
use OCA\Libresign\Db\FileUser as FileUserEntity;
use OCA\Libresign\Exception\LibresignException;
use OCA\Libresign\Helper\JSActions;
use OCA\Libresign\Service\SignFileService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Files\File;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserSession;

abstract class AEnvironmentPageAwareController extends Controller {
	private FileUserEntity $fileUserEntity;
	private FileEntity $fileEntity;
	private File $nextcloudFile;

	public function __construct(
		IRequest $request,
		protected SignFileService $signFileService,
		protected IL10N $l10n,
		private IUserSession $userSession,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * @throws LibresignException
	 */
	public function loadFileUserUuid(string $uuid): void {
		try {
			$this->fileUserEntity = $this->signFileService->getFileUser($uuid);
			$this->fileEntity = $this->signFileService->getFile(
				$this->fileUserEntity->getFileId(),
			);
		} catch (DoesNotExistException $e) {
			throw new LibresignException(json_encode([
				'action' => JSActions::ACTION_DO_NOTHING,
				'errors' => [$this->l10n->t('Invalid UUID')],
			]), AppFrameworkHttp::STATUS_NOT_FOUND);
		}
		$this->signFileService->validateSigner($uuid, $this->userSession->getUser());
		$this->nextcloudFile = $this->signFileService->getNextcloudFile(
			$this->fileEntity->getNodeId(),
		);
	}

	public function getFileUserEntity(): ?FileUserEntity {
		return $this->fileUserEntity;
	}

	public function getFileEntity(): ?FileEntity {
		return $this->fileEntity;
	}

	public function getNextcloudFile(): ?File {
		return $this->nextcloudFile;
	}
}
