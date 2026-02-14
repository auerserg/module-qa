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
use Superb\QA\Service\Commands;

class Run implements HttpPostActionInterface
{
    public const URL = 'qa_assistant/commands/run/';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Commands $commandsService,
        private readonly JsonFactory $jsonFactory,
        private readonly RequestInterface $request
    )
    {
    }

    /**
     * @return ResultInterface
     * @noinspection OffsetOperationsInspection
     */
    public function execute(): ResultInterface
    {
        try {
            $id = (int)$this->request->getParam('command_id');
            $command = $this->request->getParam('command');
            if ($id) {
                $processId = $this->commandsService->runById($id);
            } elseif (str_starts_with($command, Commands::CUSTOM_PREFIX)) {
                $command = str_replace(Commands::CUSTOM_PREFIX, '', $command);
                $commands = $this->commandsService->getCustomCommands();
                if (!isset($commands[$command])) {
                    throw new LocalizedException(__('Command is not allowed.'));
                }
                $processId = $this->commandsService->runCustom($commands[$command]);
            } else {
                if (!in_array($command, $this->commandsService->getCommandsList(), true)) {
                    throw new LocalizedException(__('Command is not allowed.'));
                }
                $processId = $this->commandsService->run($command, $this->request->getParam('args'));
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
     * @param string|array $response
     * @param string       $status
     * @return Json
     */
    public function jsonResponse($response = '', $status = 'success')
    {
        return $this->jsonFactory->create()->setData(is_array($response)
            ? array_replace($response, ['status' => $status])
            : [
                'status'  => $status,
                'message' => $response,
            ]);
    }
}

