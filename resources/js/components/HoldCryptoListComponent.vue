<template>
    <div class="container-fluid px-5">
        <div class="row">
            <div class="col-md-4">
                <b-card title="Summary">
                    <b-card-body>
                        <doughnut-chart :chart-data="dataSet"></doughnut-chart>
                    </b-card-body>
                </b-card>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-wallet"></i> Hold List</h3>
                            <b-button v-if="user_id" v-b-modal.modal-1 size="sm" variant="outline-light"><i class="fas fa-plus"></i>
                                Add Hold Stock
                            </b-button>
                        </div>
                    </div>

                    <div class="card-body p-0 overflow-auto">
                        <b-overlay :show="isCardBusy" no-wrap rounded="sm"></b-overlay>
                        <b-table :fields="cryptoFields" :items="cryptos" show-empty>
                            <template #cell(prev_day_close_price)="data">
                                {{
                                    data.item.prev_day_close_price != null ? Number(data.item.prev_day_close_price).toLocaleString() : '-'
                                }}
                            </template>
                            <template #cell(current_price)="data">
                                {{
                                    data.item.current_price != null ? Number(data.item.current_price).toLocaleString() : '-'
                                }}
                            </template>
                            <template #cell(percent_change)="data">
                                <span
                                    :class="{'badge-success' : data.item.percent_change > 0, 'badge-danger' : data.item.percent_change < 0}"
                                    class="badge">{{
                                        data.item.percent_change != null ? Number(data.item.percent_change).toLocaleString() : '-'
                                    }}</span>
                            </template>
                            <template #cell(hold_price)="data">
                                {{
                                    data.item.amount != null ? Number((data.item.amount * data.item.unit * data.item.current_price)).toLocaleString() : '-'
                                }}
                            </template>
                            <template v-if="user_id" #cell(actions)="row">
                                <div class="float-right mr-1">
                                    <b-button class="mr-1" size="sm" title="Remove"
                                              variant="danger" @click="confirmRemoveDialog(row.item, row.index)">
                                        <i class="fas fa-trash"></i>
                                    </b-button>
                                    <b-button class="mr-1" size="sm" title="Edit"
                                              variant="warning" @click="triggerUpdateForm(row.item)">
                                        <i class="fas fa-edit"></i>
                                    </b-button>
                                </div>
                            </template>
                        </b-table>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Currency in Indonesian Rupiah (IDR) -- convert with exchange rate from Bank Indonesia
                            buying price.</small>
                    </div>
                </div>
            </div>
        </div>
        <b-modal v-if="user_id" id="modal-1" hide-footer scrollable size="lg"
                 title="Add Cryptocurrency to My Wallet" @hide="resetResourceForm" @show="resetResourceForm">
            <b-overlay :show="isFormBusy" no-wrap rounded="sm"></b-overlay>
            <b-form @submit.stop.prevent="fetchCryptoResources">
                <b-input-group class="mb-3">
                    <b-form-input v-model="query" autofocus placeholder="Type crypto name or symbols"
                                  type="text"></b-form-input>
                    <b-input-group-append>
                        <b-button class="pull-right" type="submit" variant="primary">Search</b-button>
                    </b-input-group-append>
                </b-input-group>
                <b-table :busy="isFormBusy" :fields="cryptoResourceFields" :items="cryptoResources" bordered hover small
                         striped>
                    <template #cell(actions)="row">
                        <b-button class="mr-1 float-right" size="sm" title="Add"
                                  variant="success" @click="triggerAddForm(row.item, row.index)">
                            <i class="fas fa-plus"></i>
                        </b-button>
                    </template>
                </b-table>
            </b-form>
        </b-modal>
        <b-modal v-if="user_id" ref="delete-confirm" cancel-title="No" ok-title="Yes" ok-variant="danger" title="Remove Confirmation"
                 @hide="confirmRemoveCallback">Are you sure to remove this ?
        </b-modal>
        <b-modal v-if="user_id" ref="amount-form" ok-only
                 ok-title="Save" ok-variant="success"
                 title="How many do you have ?" @hide="resetUpdateForm"
                 @ok="store">
            <b-form-input type="number" v-model="updateSoon.amount" placeholder="Enter amount"></b-form-input>
        </b-modal>
    </div>
</template>

<script>
import DoughnutChart from "./Chart/DoughnutChart";

export default {
    components: {
        DoughnutChart
    },
    props: [
        'user_id'
    ],
    data() {
        return {
            cryptos: [],
            cryptoFields: [
                {'key': 'id', 'label': '#'},
                'symbol',
                'name',
                {'key': 'prev_day_close_price', 'label': 'Prev', 'tdClass': 'text-right'},
                {'key': 'current_price', 'label': 'Current', 'tdClass': 'text-right'},
                {'key': 'percent_change', 'label': '1d %', 'tdClass': 'text-right'},
                {'key': 'amount', 'label': 'Hold', 'tdClass': 'text-right'},
                {'key': 'hold_price', 'tdClass': 'text-right'},
                {'key': 'actions', 'label': ''}
            ],
            query: '',
            cryptoResources: [],
            cryptoResourceFields: [
                'symbol',
                {'key': 'name', 'label': 'Name'},
                {'key': 'actions', 'label': ''}
            ],
            deleteSoon: {},
            updateSoon: {
                index: null,
                id: null,
                amount: null
            },
            isFormBusy: false,
            isCardBusy: false
        }
    },
    created() {
        this.fetchCryptos();
        this.listenForChanges();
    },
    computed: {
        dataSet: function(){
            let labels = this.cryptos.map(a => a.name)
            let data = this.cryptos.map(a => (a.amount * a.unit * a.current_price).toFixed(2))
            let backgrounds = this.cryptos.map(() => this.dynamicColors())
            return {
                datasets: [{
                    label: 'Summary',
                    data: data,
                    backgroundColor: backgrounds,
                    hoverOffset: 4
                }],
                labels: labels
            }
        }

    },
    methods: {
        dynamicColors() {
            let r = Math.floor(Math.random() * 255);
            let g = Math.floor(Math.random() * 255);
            let b = Math.floor(Math.random() * 255);
            return "rgb(" + r + "," + g + "," + b + ")";
        },
        fetchCryptos() {
            this.isCardBusy = true
            axios.get('/api/wallet/cryptos').then((response) => {
                this.cryptos = response.data.cryptos;
                this.isCardBusy = false
            })
        },
        fetchCryptoResources() {
            this.isFormBusy = true
            axios.get('/api/wallet/get-resource-crypto', {
                params: {
                    query: this.query
                }
            }).then((response) => {
                this.cryptoResources = response.data.cryptoResources;
            }).then(() => {
                this.isFormBusy = false
            })
        },
        listenForChanges() {
            Echo.channel('my-wallet')
                .listen('WatchlistCryptoEvent', (e) => {
                    console.log(e)
                    let crypto = this.cryptos.find((crypto) => crypto.id === e.id);
                    if (crypto) {
                        let index = this.cryptos.indexOf(crypto);
                        this.cryptos[index].prev_day_close_price = e.prev_day_close_price;
                        this.cryptos[index].current_price = e.current_price;
                        this.cryptos[index].percent_change = e.percent_change;
                    } else {
                        this.cryptos.push(e)
                    }
                })
        },
        resetResourceForm() {
            this.query = ''
            this.cryptoResources = []
        },
        resetUpdateForm() {
            this.updateSoon = {
                index: null,
                id: null,
                amount: null
            }
        },
        store() {
            axios.post('/api/wallet/store-crypto', {
                id: this.updateSoon.id,
                amount: 0 + this.updateSoon.amount
            }).then((response) => {
                if (response.data.status) {
                    this.$bvToast.toast('Cryptocurrency has been added to wallet.', {
                        title: `Cryptocurrency Wallet`,
                        variant: 'success',
                        solid: true
                    })
                    if (this.updateSoon.index != null) {
                        this.$delete(this.cryptoResources, this.updateSoon.index)
                    }
                    this.fetchCryptos()
                } else {
                    this.$bvToast.toast('Something bad occur while adding to wallet.', {
                        title: `Cryptocurrency Wallet`,
                        variant: 'danger',
                        solid: true
                    })
                    console.error(response)
                }
            }).catch((error) => {
                this.$bvToast.toast('Something bad occur while adding to wallet.', {
                    title: `Cryptocurrency Wallet`,
                    variant: 'danger',
                    solid: true
                })
                console.error(error)
            })
        },
        remove({crypto, index}) {
            axios.post('/api/wallet/remove-crypto', {
                id: crypto.id
            }).then((response) => {
                if (response.data.status) {
                    this.$bvToast.toast('Cryptocurrency has been removed from wallet.', {
                        title: `Cryptocurrency Wallet`,
                        variant: 'success',
                        solid: true
                    })
                    this.$delete(this.cryptos, index)
                } else {
                    this.$bvToast.toast('Something bad occur while removing.', {
                        title: `Cryptocurrency Wallet`,
                        variant: 'danger',
                        solid: true
                    })
                    console.error(response)
                }
            }).catch((error) => {
                this.$bvToast.toast('Something bad occur while removing.', {
                    title: `Cryptocurrency Wallet`,
                    variant: 'danger',
                    solid: true
                })
                console.error(error)
            })
        },
        confirmRemoveDialog(crypto, index) {
            this.deleteSoon = {crypto, index}
            this.$refs["delete-confirm"].show()
        },
        confirmRemoveCallback(e) {
            if (e.trigger === 'ok') {
                this.remove(this.deleteSoon)
            }
        },
        triggerAddForm(crypto, index) {
            this.updateSoon.index = index
            this.updateSoon.id = crypto.id
            this.$refs["amount-form"].show()
        },
        triggerUpdateForm(crypto) {
            this.updateSoon.id = crypto.id
            this.updateSoon.amount = crypto.amount
            this.$refs["amount-form"].show()
        }
    }
}
</script>

<style scoped>

</style>
