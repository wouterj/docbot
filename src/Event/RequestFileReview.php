<?php

namespace Docbot\Event;

use Gnugat\Redaktilo\File;
use Zend\EventManager\Event;

/**
 * An event that is triggered when a file should be reviewed.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class RequestFileReview extends Event
{
    const EVENT = 'file_review_requested';

    /** @var File */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;

        parent::__construct(self::EVENT, 'docbot');
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFilename()
    {
        return $this->file->getFilename();
    }
}
