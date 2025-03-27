<?php

function removeSpecialCharacters($string)
    {
        // Remove spaces and special characters
        $string = str_replace(' ', '', $string);
        return preg_replace('/[^A-Za-z0-9]/', '', $string);
    }