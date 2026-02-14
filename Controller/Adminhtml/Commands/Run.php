<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Commands;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Superb\QA\Service\Command;

class Run implements HttpPostActionInterface
{
    public const URL = 'qa_assistant/commands/run/';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Command $commandProvider,
        private readonly JsonFactory $jsonFactory,
        private readonly RequestInterface $request
    )
    {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $id = (int)$this->request->getParam('command_id');
            $command = $this->request->getParam('command');
            if ($id) {
                $processId = $this->commandProvider->runById($id);
            } elseif (0 === strpos($command, Command::CUSTOM_PREFIX)) {
                $command = str_replace(Command::CUSTOM_PREFIX, '', $command);
                $commands = $this->commandProvider->getCustomCommands();
                if (!isset($commands[$command])) {
                    throw new LocalizedException(__('Command is not allowed.'));
                }
                $processId = $this->commandProvider->runCustom($commands[$command]);
            } else {
                if (!in_array($command, $this->commandProvider->getCommandsList())) {
                    throw new LocalizedException(__('Command is not allowed.'));
                }
                $processId = $this->commandProvider->run($command, $this->request->getParam('args'));
            }

            return $this->jsonResponse([
                'processId' => $processId,
                'message'   => __('Command run successfully.'),
            ], 'info');
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage(), 'error');
        } catch (Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage(), 'error');
        }
    }

    /**
     * @param $response
     * @param $status
     * @return Json
     */
    public function jsonResponse($response = '', $status = 'success')
    {
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData(is_array($response)
            ? array_replace($response, ['status' => $status])
            : [
                'status'  => $status,
                'message' => $response,
            ]);
    }
}

