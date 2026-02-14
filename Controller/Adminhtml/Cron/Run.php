<?php
/**
 * Copyright © Auer All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Cron;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Superb\QA\Service\Commands;
use Superb\QA\Service\Cron;

class Run implements HttpPostActionInterface
{
    public const URL = 'qa_assistant/cron/run/';
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Cron $cronProvider,
        private readonly Commands $commandsService,
        private readonly JsonFactory $jsonFactory,
        private readonly RequestInterface $request
    )
    {
    }

    public function execute()
    {
        try {
            $id = (int)$this->request->getParam('command_id');
            if ($id) {
                $processId = $this->commandsService->runById($id);
            } else {
                $cron = $this->request->getParam('cron');
                if (!in_array($cron, $this->cronProvider->getCronJobsList(), true)) {
                    throw new LocalizedException(__('Cron job is not found.'));
                }
                $processId = $this->commandsService->run('cron:job:run', $cron);
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

