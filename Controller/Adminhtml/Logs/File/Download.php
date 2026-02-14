<?php
/**
 * Copyright © Auer, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Superb\QA\Controller\Adminhtml\Logs\File;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\FileSystemException;
use Magento\ImportExport\Model\LocalizedFileName;
use Superb\QA\Controller\Adminhtml\Logs\Index;
use Superb\QA\Service\LogFile;

class Download extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Superb_QA::logs_index';
    /**
     * Url to this controller
     */
    public const URL = 'qa_assistant/logs_file/download/';
    protected $messageManager;

    /**
     * @var LocalizedFileName
     */
    private $localizedFileName;

    public function __construct(
        Action\Context $context,
        private readonly FileFactory $fileFactory,
        private readonly LogFile $logFile,
        ?LocalizedFileName $localizedFileName = null
    )
    {
        parent::__construct($context);
        $this->localizedFileName = $localizedFileName ?? ObjectManager::getInstance()->get(LocalizedFileName::class);
    }

    /**
     * Controller basic method implementation.
     *
     * @return Redirect|ResponseInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('filename');
        try {
            if ($this->logFile->isExist($fileName)) {
                return $this->fileFactory->create($this->localizedFileName->getFileDisplayName($fileName),
                    [
                        'type'  => 'filename',
                        'value' => $fileName
                    ],
                    DirectoryList::LOG);
            }
            $this->messageManager->addErrorMessage(__('%1 is not a valid file', $fileName));
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage(__('Please provide valid log file name'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath(Index::URL);
    }
}
