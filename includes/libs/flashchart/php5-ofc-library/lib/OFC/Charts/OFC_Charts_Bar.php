<?php
/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

require_once('OFC/Charts/OFC_Charts_Base.php');

class OFC_Charts_Bar_Value
{
    public function __construct($top, $bottom=null)
    {
        $this->top = $top;
        
        if (isset($bottom)) {
            $this->bottom = $bottom;
        }
    }
    
    public function set_colour($colour)
    {
        $this->colour = $colour;
    }
    
    public function set_tooltip($tip)
    {
        $this->tip = $tip;
    }
}

class OFC_Charts_Bar extends OFC_Charts_Base
{
    public function __construct()
    {
        parent::__construct();

        $this->type      = 'bar';
    }

    public function set_key($text, $size)
    {
        $this->text = $text;
        $this->{'font-size'} = $size;
    }

    public function set_values($v)
    {
        $this->values = $v;
    }

    public function append_value($v)
    {
        $this->values[] = $v;
    }

    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    public function set_alpha($alpha)
    {
        $this->alpha = $alpha;
    }

    public function set_tooltip($tip)
    {
        $this->tip = $tip;
    }
}
