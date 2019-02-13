<?php

namespace common\models;

use common\behaviors\UTCDateTimeBehavior;
use DOMDocument;
use Yii;
use yii\helpers\HtmlPurifier;

/**
 * This is the model class for PageData.
 *
 * @property \MongoDB\BSON\ObjectID|string|null $id
 * @property string $slug
 * @property string $language
 * @property string $title
 * @property string $description
 * @property string $content
 * @property string $keywords
 * @property string $thumbnail
 * @property string $status
 * @property string $created_by
 * @property string $updated_by
 * @property \MongoDB\BSON\UTCDateTime $created_at
 * @property \MongoDB\BSON\UTCDateTime $updated_at
 *
 * @property Page $page
 */
class PageData extends \yii\mongodb\ActiveRecord
{

    const STATUS_ACTIVE = 'STATUS_ACTIVE';//10;
    const STATUS_INACTIVE = 'STATUS_INACTIVE';//20;

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'core_page_data';
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            '_id',
            'slug',
            'language',
            'title',
            'description',
            'content',
            'keywords',
            'thumbnail',
            'status',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * get id
     * @return \MongoDB\BSON\ObjectID|string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            UTCDateTimeBehavior::class,
        ];
    }

    /**
     * @return int timestamp
     */
    public function getUpdatedAt()
    {
        return $this->updated_at->toDateTime()->format('U');
    }

    /**
     * @return int timestamp
     */
    public function getCreatedAt()
    {
        return $this->created_at->toDateTime()->format('U');
    }


    /**
     * get status list
     * @param null $e
     * @return array
     */
    public static function getStatusOption($e = null)
    {
        $option = [
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_INACTIVE => Yii::t('app', 'Inactive'),
        ];
        if (is_array($e))
            foreach ($e as $i)
                unset($option[$i]);
        return $option;
    }

    /**
     * get status text
     * @param null $status
     * @return string
     */
    public function getStatusText($status = null)
    {
        if ($status === null)
            $status = $this->status;
        switch ($status) {
            case self::STATUS_ACTIVE:
                return Yii::t('app', 'Active');
                break;
            case self::STATUS_INACTIVE:
                return Yii::t('app', 'Inactive');
                break;
            default:
                return Yii::t('app', 'Unknown');
                break;
        }
    }

    /**
     * color status text
     * @return mixed|string
     */
    public function getStatusColorText()
    {
        $status = $this->status;
        if ($status == self::STATUS_ACTIVE) {
            return '<span class="label label-success">' . $this->statusText . '</span>';
        }
        if ($status == self::STATUS_INACTIVE) {
            return '<span class="label label-default">' . $this->statusText . '</span>';
        }
        return $this->statusText;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug', 'language', 'title', 'description', 'content', 'keywords'], 'required', 'on' => ['update', 'create']],
            [['status'], 'string', 'max' => 255],
            [['content', 'thumbnail'], 'string'],
            [['thumbnail'], 'url'],
            [['language'], 'string', 'max' => 5],
            [['title', 'description', 'keywords'], 'string', 'max' => 160],
            [['slug'], 'string', 'max' => 100],
            [['slug'], 'unique', 'on' => ['create']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Account::class, 'targetAttribute' => ['created_by' => '_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'slug' => Yii::t('app', 'Slug'),
            'language' => Yii::t('app', 'Language'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'content' => Yii::t('app', 'Content'),
            'keywords' => Yii::t('app', 'Keywords'),
            'thumbnail' => Yii::t('app', 'Thumbnail'),
            'status' => Yii::t('app', 'Status'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getPage()
    {
        return $this->hasOne(Page::class, ['slug' => 'slug']);
    }


    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        /* text */
        $this->title = ucwords($this->title);
        $this->description = ucfirst($this->description);
        /* clean html */
        $config = [
            'HTML.MaxImgLength' => null,
            'CSS.MaxImgLength' => null,
            'HTML.Trusted' => true,
            'Filter.YouTube' => true,
            //'CSS.AllowedProperties'=>'style'
        ];
        $this->content = HtmlPurifier::process($this->content, $config);

        if (!empty(Yii::$app->user)) {
            if (empty($this->created_by)) {
                $this->created_by = Yii::$app->user->id;
            }
            $this->updated_by = Yii::$app->user->id;
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete(); // TODO: Change the autogenerated stub
        if (!$this->page->data) {
            $this->page->delete();
        }
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function beforeDelete()
    {
        $corePages = [
            'privacy',
            'terms'
        ];

        if (in_array($this->slug, $corePages)) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Sorry! This page can not be deleted.'));
            return false;
        }
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * view url
     * @param bool $absolute
     * @return string
     */
    public function getViewUrl($absolute = false)
    {
        $act = 'createUrl';
        if ($absolute) {
            $act = 'createAbsoluteUrl';
        }
        return Yii::$app->urlManager->$act(['site/page', 'id' => $this->slug, 'lang' => $this->language]);
    }

    /**
     * @return array|bool|mixed
     */
    public function getImageObject()
    {

        $doc = new DOMDocument();
        $doc->loadHTML($this->content);
        $tags = $doc->getElementsByTagName('img');
        $img = [];
        foreach ($tags as $i => $tag) {
            $img['url'] = $tag->getAttribute('src');
            $img['width'] = $tag->getAttribute('width');
            $img['height'] = $tag->getAttribute('height');
            break;
        }

        return $img;
    }

}
