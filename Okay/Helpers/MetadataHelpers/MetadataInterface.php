<?php


namespace Okay\Helpers\MetadataHelpers;


interface MetadataInterface
{
    /**
     * @return string
     */
    public function getH1();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return string
     */
    public function getMetaDescription();
}