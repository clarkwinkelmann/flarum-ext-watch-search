import {extend} from 'flarum/extend';
import app from 'flarum/app';
import SettingsPage from 'flarum/components/SettingsPage';
import NotificationGrid from 'flarum/components/NotificationGrid';
import Button from 'flarum/components/Button';
import SearchQuery from './models/SearchQuery';
import NewDiscussionNotification from './components/NewDiscussionNotification';

app.initializers.add('clarkwinkelmann-watch-search', () => {
    app.store.models['watch-search-queries'] = SearchQuery;

    app.notificationComponents.newDiscussionInSearch = NewDiscussionNotification;

    extend(NotificationGrid.prototype, 'notificationTypes', function (items) {
        items.add('newDiscussionInSearch', {
            name: 'newDiscussionInSearch',
            icon: 'fas fa-search-plus',
            label: app.translator.trans('clarkwinkelmann-watch-search.forum.settings.notify_new_discussion_label'),
        });
    });

    extend(SettingsPage.prototype, 'settingsItems', items => {
        const queries = app.store.all('watch-search-queries');

        if (!queries.length) {
            return;
        }

        items.add('watch-search-queries', m('fieldset', [
            m('legend', app.translator.trans('clarkwinkelmann-watch-search.forum.settings.title')),
            m('table.WatchSearchQueries', m('tbody', queries.map(query => m('tr', [
                m('td', {
                    onclick() {
                        const name = prompt(app.translator.trans('clarkwinkelmann-watch-search.forum.settings.new-name'), query.name());

                        if (name === query.name() || !name) {
                            return;
                        }

                        query.save({
                            name,
                        }).then(() => {
                            m.redraw();
                        });
                    },
                }, query.name()),
                m('td', m('code', query.query())),
                m('td', Button.component({
                    className: 'Button Button--danger Button--icon',
                    icon: 'fas fa-times',
                    title: app.translator.trans('clarkwinkelmann-watch-search.forum.settings.delete'),
                    onclick() {
                        query.delete().then(() => {
                            m.redraw();
                        });
                    },
                })),
            ])))),
        ]));
    });
});

/**
 * Checks if a record exists for a query string.
 * @param {string} query Query string. Must be in the same order as the existing record
 * @returns {boolean}
 */
export function queryExists(query) {
    return app.store.all('watch-search-queries').some(record => record.query() === query);
}

/**
 * Creates a new watched search from a query string.
 * @param {string} query
 * @param {string|null} name Optional name. If not specified, will be the same as the query string
 * @return {Promise}
 */
export function createQuery(query, name = null) {
    if (!name) {
        name = query;
    }

    return app.store.createRecord('watch-search-queries').save({
        name,
        query,
    });
}

/**
 * Deletes a record based on the query string. You must check the record exists before calling this method.
 * @param {string} query Query string. Must be in the same order as the existing record
 * @return {Promise}
 */
export function deleteQuery(query) {
    const record = app.store.all('watch-search-queries').find(record => record.query() === query);

    if (record) {
        return record.delete();
    }

    throw new Error('No record for query ' + query);
}
