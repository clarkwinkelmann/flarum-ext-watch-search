import {extend} from 'flarum/extend';
import app from 'flarum/app';
import PermissionGrid from 'flarum/components/PermissionGrid';
import SettingsModal from './components/SettingsModal';

app.initializers.add('clarkwinkelmann-watch-search', () => {
    //app.extensionSettings['clarkwinkelmann-watch-search'] = () => app.modal.show(new SettingsModal());

    extend(PermissionGrid.prototype, 'viewItems', items => {
        items.add('clarkwinkelmann-watch-search-use', {
            icon: 'fas fa-search-plus',
            label: app.translator.trans('clarkwinkelmann-watch-search.admin.permissions.use'),
            permission: 'watch-search.use',
        });
    });
});
