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
use Superb\QA\Model\CommandProvider;
use Superb\QA\Model\CronProvider;

class Run implements HttpPostActionInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CronProvider $cronProvider,
        private readonly CommandProvider $commandProvider,
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
                $processId = $this->commandProvider->runById($id);
            } else {
                $cron = $this->request->getParam('cron');
                if (!in_array($cron, $this->cronProvider->getCronJobsList())) {
                    throw new LocalizedException(__('Cron job is not found.'));
                }
                $processId = $this->commandProvider->run('cron:job:run', $cron);
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

