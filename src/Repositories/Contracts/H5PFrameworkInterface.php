<?php

namespace EscolaLms\HeadlessH5P\Repositories\Contracts;

use H5PFrameworkInterface as H5PFrameworkInterfaceCore;

interface H5PFrameworkInterface extends H5PFrameworkInterfaceCore
{
    public function setMainData(array $mainData): void;
    public function getDownloadFile($id);
}
