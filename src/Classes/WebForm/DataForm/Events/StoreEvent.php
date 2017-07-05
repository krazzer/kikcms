<?php

namespace KikCMS\Classes\WebForm\DataForm\Events;


class StoreEvent
{
    const BEFORE_STORE      = 'beforeStore';
    const BEFORE_MAIN_STORE = 'beforeMainStore';
    const AFTER_MAIN_STORE  = 'afterMainStore';
    const AFTER_STORE       = 'afterStore';
}