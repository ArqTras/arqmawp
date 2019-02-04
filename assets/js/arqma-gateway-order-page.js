/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function arqma_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#arqma_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function arqma_showQR(show=true) {
    jQuery('#arqma_qr_code_container').toggle(show);
}
function arqma_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': arqma_details.order_id
    };
    jQuery.get(arqma_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            arqma_details = response;
            arqma_updateDetails();
        }
    });
}

function arqma_updateDetails() {

    var details = arqma_details;

    jQuery('#arqma_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.arqma_payment_unpaid').show();
            jQuery('.arqma_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.arqma_payment_partial').show();
            jQuery('.arqma_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.arqma_payment_paid').show();
            jQuery('.arqma_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.arqma_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.arqma_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.arqma_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#arqma_exchange_rate').html('1 ARQ = '+details.rate_formatted+' '+details.currency);
    jQuery('#arqma_total_amount').html(details.amount_total_formatted);
    jQuery('#arqma_total_paid').html(details.amount_paid_formatted);
    jQuery('#arqma_total_due').html(details.amount_due_formatted);

    jQuery('#arqma_integrated_address').html(details.integrated_address);

    if(arqma_show_qr) {
        var qr = jQuery('#arqma_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#arqma_tx_table').show();
        jQuery('#arqma_tx_none').hide();
        jQuery('#arqma_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+arqma_explorer_url+'/tx/'+tx.txid+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' arqma</td>'+
                '</tr>';

            jQuery('#arqma_tx_table tbody').append(row);
        }
    } else {
        jQuery('#arqma_tx_table').hide();
        jQuery('#arqma_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = arqma_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                arqma_showNotification('Transaction received for '+new_txs[i].amount_formatted+' arqma');
            }
        }
    }

    if(details.status != arqma_order_state.status) {
        switch(details.status) {
            case 'paid':
                arqma_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                arqma_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                arqma_showNotification('Your order has expired', 'error');
                break;
        }
    }

    arqma_order_state = {
        status: arqma_details.status,
        txs: arqma_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof arqma_details !== 'undefined') {
        arqma_order_state = {
            status: arqma_details.status,
            txs: arqma_details.txs
        };
        setInterval(arqma_fetchDetails, 30000);
        arqma_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#arqma_integrated_address':
                    arqma_showNotification('Copied destination address!');
                    break;
                case '#arqma_total_due':
                    arqma_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});