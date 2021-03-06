<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2017 Power Kernel
 */


namespace common\models;

use common\Core;
use MongoDB\BSON\UTCDateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BlogSearch represents the model behind the search form about `common\models\Blog`.
 */
class BlogSearch extends Blog
{

    public $fullname;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['views', 'status'], 'safe'],
            [['language', 'title', 'desc', 'content', 'tags', 'created_by', 'created_at', 'updated_at'], 'safe'],
            [['fullname'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Blog::find();
        //$query->joinWith(['author']);


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

//        $dataProvider->sort->attributes['fullname'] = [
//            // The tables are the ones our relation are configured to
//            // in my case they are prefixed with "tbl_"
//            'asc' => ['{{%core_account}}.fullname' => SORT_ASC],
//            'desc' => ['{{%core_account}}.fullname' => SORT_DESC],
//        ];

        /* user's blog */
        if (Core::checkMCA(null, 'blog', 'manage')) {
            $query->andFilterWhere([
                'created_by' => Yii::$app->user->id,
            ]);
        }

        /* list all public blog */
        if (Core::checkMCA(null, 'blog', 'index')) {
            $query->andFilterWhere([
                'status' => Blog::STATUS_PUBLISHED,
            ]);
        }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if (!empty($this->created_by)) {

            $key = '_id';
            $ids = [];
            $owners = Account::find()->select([$key])->where(['like', 'fullname', $this->created_by])->asArray()->all();
            foreach ($owners as $owner) {
                $ids[] = (string)$owner[$key];

            }
            $query->andFilterWhere(['created_by' => $ids]);
        }

        $query->andFilterWhere([
            //'id' => $this->id,
            //'created_by' => $this->created_by > 0 ? (int)$this->created_by : null,
            'views' => $this->views > 0 ? (int)$this->views : null,
        ]);

        $query->andFilterWhere(['like', 'language', $this->language])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'tags', $this->tags]);


        if (!empty($this->updated_at)) {
            $query->andFilterWhere([
                'updated_at' => ['$gte' => new UTCDateTime(strtotime($this->updated_at) * 1000)],
            ])->andFilterWhere([
                'updated_at' => ['$lt' => new UTCDateTime((strtotime($this->updated_at) + 86400) * 1000)],
            ]);
        }

        return $dataProvider;
    }


}
