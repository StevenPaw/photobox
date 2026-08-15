<?php

use SilverStripe\CMS\Model\SiteTree;

/**
 * silverstripe/linkfield depends on silverstripe/cms, which expects a global
 * Page class to exist (e.g. RedirectorPage extends Page). This project has
 * no page tree of its own, but this class needs to exist for the CMS module
 * to boot.
 *
 * @mixin \SilverStripe\Assets\Shortcodes\FileLinkTracking
 * @mixin \SilverStripe\Assets\AssetControlExtension
 * @mixin \SilverStripe\CMS\Model\SiteTreeLinkTracking
 * @mixin \SilverStripe\Versioned\RecursivePublishable
 * @mixin \SilverStripe\Versioned\VersionedStateExtension
 */
class Page extends SiteTree
{
}
