<?php

namespace Networkteam\Neos\NodeTypePlaceholder\NodeTypePostprocessor;

use Flowpack\NodeTemplates\Service\EelException;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\NodeTypePostprocessor\NodeTypePostprocessorInterface;
use Neos\Eel\CompilingEvaluator;
use Neos\Eel\ParserException;
use Neos\Eel\Utility as EelUtility;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Annotations as Flow;

/***************************************************************
 *  (c) 2025 networkteam GmbH - all rights reserved
 ***************************************************************/

/**
 * Parse EEL expression in placeholder editor option of nodeType property.
 *
 * Example NodeType configuration:
 *
 *   My.Package:ExampleNode:
 *     properties:
 *       suggestionLimit:
 *         ui:
 *           inspector:
 *             editorOptions:
 *               placeholder: '${"Defaults to " + Configuration.setting("My.Package.suggestionLimit")}'
 */
class PlaceholderPostprocessor implements NodeTypePostprocessorInterface
{
    /**
     * @Flow\Inject(lazy=false)
     * @var CompilingEvaluator
     */
    protected $eelEvaluator;

    /**
     * @Flow\InjectConfiguration(path="defaultEelContext")
     * @var array
     */
    protected $defaultContext;

    /**
     * @var array
     */
    protected $defaultContextVariables;

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    public function process(NodeType $nodeType, array &$configuration, array $options)
    {
        if ($this->defaultContextVariables === null) {
            $this->defaultContextVariables = EelUtility::getDefaultContextVariables($this->defaultContext);
        }
        $contextVariables = $this->defaultContextVariables;

        if (isset($configuration['properties']) && is_array($configuration['properties'])) {
            foreach ($configuration['properties'] as $propertyName => &$propertyConfiguration) {

                if (!isset($propertyConfiguration['ui']['inspector']['editorOptions']['placeholder'])) {
                    continue;
                }

                $expression = $propertyConfiguration['ui']['inspector']['editorOptions']['placeholder'];
                if (is_string($expression) && str_starts_with($expression, '${') && str_ends_with($expression, '}')) {
                    try {
                        $placeholder = EelUtility::evaluateEelExpression($expression, $this->eelEvaluator, $contextVariables);
                        if (!empty($placeholder)) {
                            $propertyConfiguration['ui']['inspector']['editorOptions']['placeholder'] = $placeholder;
                        }
                    } catch (ParserException $parserException) {
                        throw new EelException('EEL Expression in NodeType property placeholder string could not be parsed.', 1741956629, $parserException);
                    } catch (\Exception $exception) {
                        throw new EelException(sprintf('EEL Expression "%s" in NodeType property placeholder caused an error.', $expression), 1741956683, $exception);
                    }
                }
            }
        }
    }
}