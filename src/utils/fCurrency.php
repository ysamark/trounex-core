<?php

if (!function_exists ('fCurrency')) {
  /**
   * fCurrency function
   *
   * This function formats a given monetary value into the specified currency.
   *
   * @param float $amount       The monetary value to be formatted.
   * @param string $currency    The currency code (default: USD).
   *
   * @return string             The formatted monetary value in the specified currency.
   *
   * @author Agostinho Lopes
   * @version 1.0
   */
  function fCurrency ($amount) {
    $decimalPlaces = null;
    $decimalPlacesRe = '/(\.([0-9]+))$/';
    $amount = (string)(!is_numeric ($amount) ? 0 : $amount);

    $args = func_get_args ();

    $strChars = (function (string $str): array {
      $chars = [];

      for ($i = 0; $i < strlen ($str); $i++) {
        array_push ($chars, $str[$i]);
      }

      return $chars;
    });

    $fetchArg = (function (array $args, $argDefaultValue, Closure $argFilter) {
      static $fetchedArgsIndexes = [];

      for ($i = 1; $i < count ($args); $i++) {
        if (in_array ($i, $fetchedArgsIndexes)) {
          continue;
        }

        if (call_user_func ($argFilter, $args[$i])) {
          array_push ($fetchedArgsIndexes, $i);
          return $args[$i];
        }
      }

      return $argDefaultValue;
    });

    $currency = call_user_func ($fetchArg, $args, 'USD', function ($currency) {
      return is_string ($currency);
    });

    $currencyAtTheEnd = call_user_func ($fetchArg, $args, false, function ($currencyAtTheEnd) {
      return is_bool ($currencyAtTheEnd);
    });

    $decimalPlacesLen = call_user_func ($fetchArg, $args, 2, function ($decimalPlacesLen) {
      return is_int ($decimalPlacesLen);
    });

    if (preg_match ($decimalPlacesRe, $amount, $decimalPlacesMatch)) {
      $decimalPlaces = trim ($decimalPlacesMatch[2]);
      $amount = preg_replace ($decimalPlacesRe, '', $amount);
    }

    if (strlen ($decimalPlaces) >= $decimalPlacesLen) {
      $decimalPlacesChars = call_user_func ($strChars, (string)$decimalPlaces);

      $decimalPlaces = join ('', array_slice ($decimalPlacesChars, 0, $decimalPlacesLen));
    } else {
      $decimalPlaces .= str_repeat ('0', $decimalPlacesLen - strlen ($decimalPlaces));
    }

    $currentTrio = [];
    $trios = [];

    $amountValue = call_user_func ($strChars, $amount);

    for ($i = -1 + strlen ($amount); $i >= 0; $i--) {
      $currentTrio = array_merge ([$amount[$i]], $currentTrio);

      if (count ($currentTrio) >= 3) {
        array_push ($trios, join ('', $currentTrio));
        $currentTrio = [];

        array_splice($amountValue, $i, $i + 3);
      }
    }

    return (string)(
      (!$currencyAtTheEnd ? "$currency " : '')
      . (count ($amountValue) >= 1 ? join ('', $amountValue) . ((count ($trios) >= 1) ? '.' : '') : '')
      . (count ($trios) >= 1 ? join ('.', array_reverse ($trios)) : '')
      . (!empty ($decimalPlaces) ? join ('', [',', $decimalPlaces]) : '')
      . ($currencyAtTheEnd ? " $currency" : '')
    );
  }
}
