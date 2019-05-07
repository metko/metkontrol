<?php
/**
    * convertPipeToArrayPermisssion
    *
    * @param  mixed $pipeString
    *
    * @return void
    */
   if( ! function_exists("checkPipeToArray")){
      function checkPipeToArray($string)
      {
            if (is_string($string) && false !== strpos($string, '|')) {
                  return convertPipeToArray($string);
            }
            return $string;
      }
   }

   /**
    * convertPipeToArrayPermisssion
    *
    * @param  mixed $pipeString
    *
    * @return void
    */
   if( ! function_exists("convertPipeToArray")){
      function convertPipeToArray(string $pipeString)
      {
         $pipeString = trim($pipeString);
         if (strlen($pipeString) <= 2) {
               return $pipeString;
         }
         $quoteCharacter = substr($pipeString, 0, 1);
         $endCharacter = substr($quoteCharacter, -1, 1);
         if ($quoteCharacter !== $endCharacter) {
               return explode('|', $pipeString);
         }
         if (! in_array($quoteCharacter, ["'", '"'])) {
               return explode('|', $pipeString);
         }
         return explode('|', trim($pipeString, $quoteCharacter));
      }
   }

   