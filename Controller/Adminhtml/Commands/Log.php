<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Commands;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Superb\QA\Model\CommandProvider;

class Log implements HttpGetActionInterface
{
    public const URL = 'qa_assistant/commands/log/';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CommandProvider $commandProvider,
        private readonly JsonFactory $jsonFactory,
        private readonly RequestInterface $request
    )
    {
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            $id = $this->request->getParam('id');
            $entity = $this->commandProvider->getProcess($id);
            $pid = (int)$entity->getPid();
            $isRunning = false;
            if ($pid > 0) {
                $isRunning = posix_kill($pid, 0);
                $this->commandProvider->updateStatusProcess($entity, $isRunning);
                $message = $isRunning
                    ? __('The command is execute...')
                    : __('This command is complete');
            } else {
                $message = __('PID process not found');
            }
            $logData = '';
            if (file_exists($entity->getLog())) {
                $logData = file_get_contents($entity->getLog());
            }
            return $this->jsonResponse([
                'log'       => trim($logData),
                'isRunning' => $isRunning,
                'message'   => $message,
            ]);
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

