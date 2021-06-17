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
                    // point to local functions
                    $data = [];
                    $call = '$data=' . str_replace('$_smarty_tpl->tpl_vars[\'this\']->value->', '$this->pseudo_', $token->parameter['value']) . ';';
                    //echo $call . "\n";
                    try {
                        eval($call);
                    } catch (\Exception $e) {
                        //file_put_contents('/Users/mac/Downloads/token.txt', print_r($call, true));
                        //echo $e->getMessage();
                    }
                    //print_r($data);

                    if (is_array($data)) {
                        if (!empty($data['context'])) {
                            $tags[] = new TranslateTag($data['string'], [[TranslateTag::CONTEXT => $data['context']]], $token->line);
                        } else {
                            $tags[] = new TranslateTag($data['string'], [], $token->line);
                        }
                    }
                }
            }
        }

        return $tags;
    }

    private function pseudo_translate($string = null, $args = null, $alternativeLang = null, $context = null)
    {
        return [
            'string'  => $string,
            'context' => $context,
        ];
    }

    // ignore-stuff
    private function pseudo_staticUrl(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }
    private function pseudo_serverUrl(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }
    private function pseudo_hyphenator(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }
    private function pseudo_UrlViewHelper(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }
    private function pseudo_layout(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }
    private function pseudo_Logo(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }
    private function pseudo_MembershipStateYearsIcon(
        $arg0 = null,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null
    ) {
        return null;
    }

}