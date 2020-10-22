<?php

/**
 * TurboDepot is a general purpose multi storage library (ORM, Logs, Users, Files, Objects)
 *
 * Website : -> http://www.turbodepot.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2019 Edertone Advanded Solutions (08211 Castellar del Vallès, Barcelona). http://www.edertone.com
 */


namespace org\turbodepot\src\main\php\managers;

use UnexpectedValueException;
use Throwable;
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\model\MarkDownBlogPostObject;


/**
 * MarkDownBlogManager class
 */
class MarkDownBlogManager extends BaseStrictClass{


    /**
     * Defines the path to the root of the blog folders
     */
    private $_rootPath = '';


    /**
     * A filesManager instance used to operate with the blog files
     *
     * @var FilesManager
     */
    private $_fm;


    /**
     * A markdownManager instance used to operate with markdown data
     *
     * @var MarkDownManager
     */
    private $_markDownManager;


    /**
     * Contains functionalities to manage a lightweight blog based on markdown .md files, using the standard file system
     * as the blog storage.
     *
     * To publish posts to this blog, the following folder structure must be used:
     * $_rootPath/year/month/day/language-postkeywords/text.md (Where language is a two digit locale)
     *
     * @param string $rootPath A full filesystem path to the root of the folder where the blog structure is located.
     *
     */
    public function __construct(string $rootPath){

       $this->_rootPath = StringUtils::formatPath($rootPath);

       if(!is_dir($this->_rootPath)){

           throw new UnexpectedValueException('rootPath is not a valid directory: '.$rootPath);
       }

       $this->_fm = new FilesManager();
       $this->_markDownManager = new MarkDownManager();
    }


    /**
     * Obtain the data for the specified blog post
     *
     * @param string $date The date for the blog post we are looking for, in a 'yyyy-mm-dd' format
     * @param string $language A two digits string representing the language for the blog post we are requesting
     * @param string $keywords THe blog post keywords as they are defined on it's filesystem folder name. It may not be necessary to provide
     *        here the exact post folder keywords string depending on the $minimumSimilarity parameter value
     * @param number $minKeywordsSimilarity Specifies the minimum percentage of keywords similarity that we accept for an existing
     *        same date blog post to be returned by this method. a value of 100 means we will only accept the exact same keywords
     *        and in the same order for an existing post, while a value of 0 means no restrictions. In any case, the blog post that is
     *        closer to the requested keywords will be obtained. If no post meets the minimum similarity, none will be retrieved.
     *
     * @return MarkDownBlogPostObject A post instance with the requested post data or null if post was not found
     */
    public function getPost(string $date, string $language, string $keywords, int $minKeywordsSimilarity = 20){

        if(count($dateParts = explode('-', $date)) !== 3){

            throw new UnexpectedValueException('Invalid date');
        }

        $postFolder = $language.'-'.$keywords;
        $postRoot = $dateParts[0].DIRECTORY_SEPARATOR.$dateParts[1].DIRECTORY_SEPARATOR.$dateParts[2];

        $post = $this->createPostInstanceFromPath($postRoot.DIRECTORY_SEPARATOR.$postFolder);

        // If no strict match is found, we will try to find the post that is closer to the provided keywords
        if($post === null){

            try {

                $dirs = $this->_fm->getDirectoryList($this->_rootPath.DIRECTORY_SEPARATOR.$postRoot, 'mDateDesc');

                $minSimilarity = $minKeywordsSimilarity;
                $similarDirName = '';

                foreach ($dirs as $dir) {

                    $similarity = StringUtils::compareSimilarityPercent($dir, $postFolder);

                    // If the folder name similarity to the requested one is higher than the current minimum, it is accepted
                    if($similarity > $minSimilarity){

                        $similarDirName = $dir;
                        $minSimilarity = $similarity;
                    }
                }

                // The most acceptable dir name will be specified here if anyone was found
                if($similarDirName !== ''){

                    $post = $this->createPostInstanceFromPath($postRoot.DIRECTORY_SEPARATOR.$similarDirName);
                }

            } catch (Throwable $e) {

                throw new UnexpectedValueException('Could not find a blog post with the specified criteria');
            }
        }

        return $post;
    }


    /**
     * Get a list of MarkDownBlogPostObject instances containing the $count newest available blog posts.
     *
     * @param string $language A two digit string containing the locale we want for the obtained posts
     * @param string $count The max number of latest posts we want to obtain
     *
     * @return array A list with the $count number of latest blog posts instances, sorted by newest first
     */
    public function getLatestPosts(string $language, int $count){

        if($count <= 0){

            throw new UnexpectedValueException('count must be a positive integer');
        }

        if(empty($years = $this->_fm->getDirectoryList($this->_rootPath, 'nameDesc'))){

            return [];
        }

        $years = $this->_fm->getDirectoryList($this->_rootPath, 'nameDesc');

        return $this->_getLatestPostsRecursive($language, 0, 0, 0, $years, null, null, $count, []);
    }


    /**
     * Auxiliary method for getLatestPosts to obtain the list of latest blog posts recursively
     *
     * @param string $language The requested 2 digit language
     * @param int $yearIndex The initial index for the years array to start looking for
     * @param int $monthIndex The initial index for the months array to start looking for
     * @param int $dayIndex The initial index for the days array to start looking for
     * @param array $years Array with the list of years from blog root
     * @param array|null $months Array with the list of months for the current year index
     * @param array|null $days Array with the list of days for the current month index
     * @param int $count The max number of posts to obtain
     * @param array $result An array that will be populated with the result
     *
     * @return array A list with the $count number of latest blog posts instances, sorted by newest first
     */
    private function _getLatestPostsRecursive(string $language, int $yearIndex, int $monthIndex, int $dayIndex,
        array $years, $months, $days, int $count, array $result){

        $sep = DIRECTORY_SEPARATOR;

        if($months === null && strlen($years[$yearIndex]) === 4){

            $months = $this->_fm->getDirectoryList($this->_rootPath.$sep.$years[$yearIndex], 'nameDesc');
        }

        if($days === null && isset($months[$monthIndex]) && strlen($months[$monthIndex]) === 2){

            $days = $this->_fm->getDirectoryList($this->_rootPath.$sep.$years[$yearIndex].$sep.$months[$monthIndex], 'nameDesc');
        }

        if(isset($years[$yearIndex]) && isset($months[$monthIndex]) && isset($days[$dayIndex])){

            $path = $years[$yearIndex].$sep.$months[$monthIndex].$sep.$days[$dayIndex];

            if($this->_fm->isDirectory($this->_rootPath.$sep.$path)){

                $posts = $this->_fm->getDirectoryList($this->_rootPath.$sep.$path, 'mDateDesc');

                foreach ($posts as $post) {

                    if(substr($post, 0, 2) === $language){

                        $result[] = $this->createPostInstanceFromPath($path.$sep.$post);

                        if(count($result) >= $count){

                            return $result;
                        }
                    }
                }
            }
        }

        if($days !== null && $dayIndex < count($days) - 1){

            return $this->_getLatestPostsRecursive($language, $yearIndex, $monthIndex, $dayIndex + 1, $years, $months, $days, $count, $result);
        }

        if($months !== null && $monthIndex < count($months) - 1){

            return $this->_getLatestPostsRecursive($language, $yearIndex, $monthIndex + 1, 0, $years, $months, null, $count, $result);
        }

        if($yearIndex < count($years) - 1){

            return $this->_getLatestPostsRecursive($language, $yearIndex + 1, 0, 0, $years, null, null, $count, $result);
        }

        return $result;
    }


    /**
     * Obtain a MarkDownBlogPostObject instace from a given blog post filesystem path.
     *
     * @param string $postPath Full path to the folder that contains the blog post, starting at the blog root folder.
     *
     * @example Given a post path like the following: "2018/05/10/en-some-keywords-text-here" based on the main blog root folder, this
     *          method will return a blog post instance with all the blog data loaded and ready to use
     *
     * @return MarkDownBlogPostObject An instance containing all the blog post data or null if post could not be found
     */
    private function createPostInstanceFromPath($postPath){

        $pathParts = explode('/', ltrim(StringUtils::formatPath($postPath, '/'), '/'));

        $post = new MarkDownBlogPostObject();

        $post->date = $pathParts[0].'-'.$pathParts[1].'-'.$pathParts[2];

        $post->language = substr($pathParts[3], 0, 2);

        $post->keywords = substr($pathParts[3], 3);

        $post->keywordsAsArray = explode('-', $post->keywords);

        try {

            $post->text = $this->_fm->readFile($this->_rootPath.DIRECTORY_SEPARATOR.$postPath.DIRECTORY_SEPARATOR.'text.md');

        } catch (Throwable $e) {

            return null;
        }

        $post->textAsHtml = $this->_markDownManager->toHtml($post->text);

        $lines = StringUtils::getLines($post->text);

        foreach ($lines as $line) {

            // Get the post title from the markdown text
            if($post->title === ''){

                if(substr_count($line, '#') > 0){

                    $post->title = ltrim($line, ' #');
                }

            }else if(substr_count($line, '#') > 0 && strlen($post->metaDescription .= ' '.ltrim($line, ' #')) >= 150){

                $post->metaDescription = StringUtils::limitLen(trim($post->metaDescription), 150);
                break;
            }
        }

        // Calculate the metadata title
        $post->metaTitle = $post->title;

        if(strlen($post->metaTitle) > 80){

            $post->metaTitle = StringUtils::removeWordsShorterThan($post->title);

            if(strlen($post->metaTitle) > 80){

                $post->metaTitle = implode(' ', $post->keywordsAsArray);
            }
        }

        return $post;
    }
}

?>