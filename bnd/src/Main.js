Ext.ns('Defitech');

Defitech.Main = {

    nb: 25,

    config: function() {
        return {
            controller: 'lib/controller.php'
        };
    },

    getJson: function(response) {
        try {
            var json = eval('(' + response.responseText + ')');
            if (!json.success) {
                Ext.Msg.show({
                    title: Defitech.wording.error_title,
                    msg: json.error
                });
            }
            return json;
        } catch (e) {
            Ext.Msg.show({
                title: Defitech.wording.error_title,
                msg: Defitech.wording.bad_json
            });
            return {
                success: false
            }
        }
    },

    failure: function(response) {
        Ext.Msg.show({
            title: Defitech.wording.error_title,
            msg: Defitech.wording.failure
        });
    },

    failureForm: function(json) {
        Ext.Msg.show({
            title: Defitech.wording.error_title,
            msg: json.error || json.msg || Defitech.wording.error_title
        });
    }
};
