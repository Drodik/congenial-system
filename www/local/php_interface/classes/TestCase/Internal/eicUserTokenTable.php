<?php

namespace TestCase\Internal;

use \Bitrix\Main\ORM,
    \Bitrix\Main\ORM\Fields;

class eicUserTokenTable extends ORM\Data\DataManager
{
    public static function getTableName()
    {
        return 'b_eic_user_token';
    }

    public static function getMap()
    {
        return [
            (new Fields\IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new Fields\StringField('TOKEN'))
                ->configureRequired(),

            (new Fields\IntegerField('USER_ID'))
        ];
    }
}
