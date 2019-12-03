<?php

namespace Drupal\status_check\Services;

class TwigToStringService {

  public $string;
  public $scanned_tokens;

  public function replace($string, $data) {
    $this->string = $string;
    $this->scanned_tokens = $this->getTokensFromString($this->string);

    /** When we need new twig structure like "for", "foreach", etc..., put it here. */
    $replacers = [
      'if' => [
        [
          'replace' => '{% if [',
          'with' => '{% if ',
        ], [
          'replace' => ':',
          'with' => '.',
        ], [
          'replace' => '] %}',
          'with' => ' %}',
        ],
      ],
      'elseif' => [
        [
          'replace' => '{% elseif [',
          'with' => '{% elseif ',
        ], [
          'replace' => ':',
          'with' => '.',
        ], [
          'replace' => '] %}',
          'with' => ' %}',
        ]
      ],
      'twig_variables' => [
        [
          'replace' => '[',
          'with' => '{{ ',
        ], [
          'replace' => ':',
          'with' => '.',
        ], [
          'replace' => ']',
          'with' => ' }}',
        ]
      ],
    ];

    foreach ($replacers as $replacer) {
      $this->specificReplace($replacer[0], $replacer[1], $replacer[2]);
    }

    return \Drupal::service('twig')->renderInline($this->string, $data);
  }

  protected function specificReplace($left = ['replace' => '', 'with' => ''], $middle = ['replace' => '', 'with' => ''], $right = ['replace' => '', 'with' => '']) {
    foreach ($this->scanned_tokens as $scanned_token) {
      foreach ($scanned_token as $key => $value) {

        /** We don`t need to put prefix and suffix for variables-twig syntax: {{ $value }}, so exclude that from this operation. */
        if ($left['replace'] !== '[') {
          $value = $left['with'] . $value . $right['with'];
        }

        $origin = $value;
        $value = str_replace($left['replace'], '', $left['with'] . $value);
        $value = str_replace($right['replace'], '', $value . $right['with']);
        $value = str_replace($middle['replace'], $middle['with'], $value);
        $this->string = str_replace($origin, $value, $this->string);
      }
    }
  }

  public function getTokensFromString($string) {
    return \Drupal::service('token')->scan($string);
  }

}
