<?php

namespace Laminas\Db\Pgsql;

Class test_type_a {

}

Class test_type_b extends test_type_a {

}

Class test {
    protected ?test_type_a $type = null;
}

Class test_b extends test {
    protected ?test_type_b $type = null;
}

