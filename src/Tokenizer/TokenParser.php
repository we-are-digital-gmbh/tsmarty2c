<?php

/*
 * This file is part of the smarty-gettext/tsmarty2c package.
 *
 * @copyright (c) Elan Ruusamäe
 * @license BSD
 * @see https://github.com/smarty-gettext/tsmarty2c
 *
 * For the full copyright and license information,
 * please see the LICENSE and AUTHORS files
 * that were distributed with this source code.
 */

namespace SmartyGettext\Tokenizer;

use Smarty;
use SmartyGettext\Tokenizer\Tag\TranslateTag;

class TokenParser
{
    /** @var Tokenizer */
    private $tokenizer;

    public function __construct(Smarty $smarty)
    {
        $this->tokenizer = new Tokenizer($smarty);
    }

    /**
     * Get translate tags from $templateFile
     *
     * @param string $templateFile
     *
     * @return TranslateTag[]
     */
    public function getTranslateTags($templateFile)
    {
        $tokens = $this->tokenizer->getTokens($templateFile);

        return $this->processTokens($tokens);
    }

    /**
     * Process tokens into TranslateTag objects
     *
     * @param array $tokens
     *
     * @return TranslateTag[]
     */
    private function processTokens($tokens)
    {
        $tags  = [];
        $topen = null;
        foreach ($tokens as $i => $token) {
            $previous = $i > 0 ? $tokens[$i - 1] : null;
            if ($token instanceof Token\Tag && $token->name === 't') {
                $topen = $token;
            } elseif ($topen &&
                ($token instanceof Token\Tag && $token->name === 'tclose')
                && $previous instanceof Token\Text
            ) {
                $tags[] = new TranslateTag($previous->text, $topen->arguments, $topen->line);
                $topen  = null;
            }

            // Laminas $this->translate()-detection incl. a optional context on position 4
            // TODO: make this configurable based on Keywords like 'translate:1,4c'
            if ($token instanceof Token\Tag && $token->name === 'private_print_expression' && !empty($token->parameter['value'])) {
                if (preg_match('/translate\(/', $token->parameter['value'])) {
                    if (preg_match('/translate\([\'\"](.*)[\'\"].*?[\'\"](.*)[\'\"]\)$/U', $token->parameter['value'], $matches)) {
                        // translation-string + context
                        $tags[] = new TranslateTag($matches[1], [[TranslateTag::CONTEXT => $matches[2]]], $token->line);
                    } elseif (preg_match('/translate\([\'\"](.*)[\'\"]/U', $token->parameter['value'], $matches)) {
                        // Fallback: only the translation-string
                        $tags[] = new TranslateTag($matches[1], [], $token->line);
                    }
                }
            }
        }

        return $tags;
    }
}