import Model from 'flarum/Model';

export default class SearchQuery extends Model {
    name = Model.attribute('name');
    query = Model.attribute('query');
}
