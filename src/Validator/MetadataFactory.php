<?php

namespace Docbot\Validator;

use Symfony\Component\Validator\Exception;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * A special MetadataFactory, providing all reviewer constraints.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /** {@inheritdoc} */
    public function getMetadataFor($value)
    {
        $metadata = new ClassMetadata(get_class($value));

        $this->registerChecks($metadata, $this->getSymfonyChecks(), 'symfony');
        $this->registerChecks($metadata, $this->getDocChecks(), 'doc');
        $this->registerChecks($metadata, $this->getRstChecks(), 'rst');

        return $metadata;
    }

    private function registerChecks(ClassMetadata $metadata, array $checks, $groupName)
    {
        foreach ($checks as $check) {
            $metadata->addConstraint(new $check(array('groups' => $groupName)));
        }
    }

    private function getSymfonyChecks()
    {
        return array(
            'Docbot\Reviewer\Check\UnstyledAdmonitions',
            'Docbot\Reviewer\Check\ShortPhpSyntax',
        );
    }

    private function getDocChecks()
    {
        return array(
            'Docbot\Reviewer\Check\TitleCase',
            'Docbot\Reviewer\Check\FirstPerson',
            'Docbot\Reviewer\Check\TitleLevel',
            'Docbot\Reviewer\Check\LineLength',
            'Docbot\Reviewer\Check\SerialComma',
        );
    }

    private function getRstChecks()
    {
        return array(
            'Docbot\Reviewer\Check\TrailingWhitespace',
            'Docbot\Reviewer\Check\FaultyLiterals',
            'Docbot\Reviewer\Check\DirectiveWhitespace',
            'Docbot\Reviewer\Check\TitleUnderline',
        );
    }

    /** {@inheritdoc} */
    public function hasMetadataFor($value)
    {
        if (!is_object($value)) {
            return false;
        }

        return $value === 'Gnugat\Redaktilo\Text';
    }
}
