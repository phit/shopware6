import template from './unzer-payment-actions.html.twig';

const { Component, Mixin } = Shopware;

Component.register('unzer-payment-actions', {
    template,

    inject: ['UnzerPaymentService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSuccessful: false,
            transactionAmount: 0.00
        };
    },

    props: {
        transactionResource: {
            type: Object,
            required: true
        },

        paymentResource: {
            type: Object,
            required: true
        },

        decimalPrecision: {
            type: Number,
            required: true,
            default: 2
        }
    },

    computed: {
        isChargePossible: function () {
            return this.transactionResource.type === 'authorization';
        },

        isRefundPossible: function () {
            return this.transactionResource.type === 'charge';
        },

        maxTransactionAmount() {
            let transactionVal = 0;

            if (this.isRefundPossible) {
                transactionVal = this.transactionResource.amount;
            } else if (this.isChargePossible) {
                transactionVal = this.paymentResource.amount.remaining;
            }

            return transactionVal;
        },

        decimalQuantity() {
            return 10 ** this.decimalPrecision;
        }
    },

    created() {
        this.transactionAmount = this.maxTransactionAmount;
    },

    methods: {
        charge() {
            this.isLoading = true;

            this.UnzerPaymentService.chargeTransaction(
                this.paymentResource.orderId,
                this.transactionResource.id,
                this.transactionAmount
            ).then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('unzer-payment.paymentDetails.notifications.chargeSuccessTitle'),
                    message: this.$tc('unzer-payment.paymentDetails.notifications.chargeSuccessMessage')
                });

                this.isSuccessful = true;

                this.$emit('reload');
            }).catch((errorResponse) => {
                let message = errorResponse.response.data.message;

                if (message === 'generic-error') {
                    message = this.$tc('unzer-payment.paymentDetails.notifications.genericErrorMessage');
                }

                this.createNotificationError({
                    title: this.$tc('unzer-payment.paymentDetails.notifications.chargeErrorTitle'),
                    message: message
                });

                this.isLoading = false;
            });
        },

        refund() {
            this.isLoading = true;

            this.UnzerPaymentService.refundTransaction(
                this.paymentResource.orderId,
                this.transactionResource.id,
                this.transactionAmount
            ).then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('unzer-payment.paymentDetails.notifications.refundSuccessTitle'),
                    message: this.$tc('unzer-payment.paymentDetails.notifications.refundSuccessMessage')
                });

                this.isSuccessful = true;

                this.$emit('reload');
            }).catch((errorResponse) => {
                let message = errorResponse.response.data.message;

                if (message === 'generic-error') {
                    message = this.$tc('unzer-payment.paymentDetails.notifications.genericErrorMessage');
                }

                this.createNotificationError({
                    title: this.$tc('unzer-payment.paymentDetails.notifications.refundErrorTitle'),
                    message: message
                });

                this.isLoading = false;
            });
        }
    }
});
