<?php

namespace App\Helpers;


class MsgFormatterHelper
{
    public function format($erorrs)
    {
        $data = [];
        for ($i = 0; $i < sizeof($erorrs); $i++) {
            if ($i + 1 < sizeof($erorrs)) {
                array_push($data, $erorrs[$i].", ");
            } else {
                array_push($data, $erorrs[$i].".");
            }
        }
        return $data;
    }
}
