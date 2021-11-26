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
                            <b-button v-b-modal.modal-1 size="sm" variant="outline-light"><i class="fas fa-plus"></i>
                                Add Hold Stock
                            </b-button>
                        </div>
                    </div>

                    <div class="card-body p-0 overflow-auto">
                        <b-overlay :show="isCardBusy" no-wrap rounded="sm"></b-overlay>
                        <b-table :fields="commodityFields" :items="commodities" show-empty>
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
                            <template #cell(actions)="row">
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
        <b-modal id="modal-1" hide-footer scrollable size="lg"
                 title="Add Indonesia Stock to My Wallet" @hide="resetResourceForm" @show="resetResourceForm">
            <b-overlay :show="isFormBusy" no-wrap rounded="sm"></b-overlay>
            <b-form @submit.stop.prevent="fetchCommodityResources">
                <b-input-group class="mb-3">
                    <b-form-input v-model="query" autofocus placeholder="Type commodity name or symbols"
                                  type="text"></b-form-input>
                    <b-input-group-append>
                        <b-button class="pull-right" type="submit" variant="primary">Search</b-button>
                    </b-input-group-append>
                </b-input-group>
                <b-table :busy="isFormBusy" :fields="commodityResourceFields" :items="commodityResources" bordered hover small
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
        <b-modal ref="delete-confirm" cancel-title="No" ok-title="Yes" ok-variant="danger" title="Remove Confirmation"
                 @hide="confirmRemoveCallback">Are you sure to remove this ?
        </b-modal>
        <b-modal ref="amount-form" ok-only
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
            commodities: [],
            commodityFields: [
                {'key': 'id', 'label': '#'},
                'name',
                {'key': 'prev_day_close_price', 'label': 'Prev', 'tdClass': 'text-right'},
                {'key': 'current_price', 'label': 'Current', 'tdClass': 'text-right'},
                {'key': 'percent_change', 'label': '1d %', 'tdClass': 'text-right'},
                {'key': 'amount', 'label': 'Hold', 'tdClass': 'text-right'},
                {'key': 'hold_price', 'tdClass': 'text-right'},
                {'key': 'actions', 'label': ''}
            ],
            query: '',
            commodityResources: [],
            commodityResourceFields: [
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
        this.fetchCommoditys();
        this.listenForChanges();
    },
    computed: {
        dataSet: function(){
            let labels = this.commodities.map(a => a.name)
            let data = this.commodities.map(a => (a.amount * a.unit * a.current_price).toFixed(2))
            let backgrounds = this.commodities.map(() => this.dynamicColors())
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
        fetchCommoditys() {
            this.isCardBusy = true
            axios.get('/api/wallet/commodities').then((response) => {
                this.commodities = response.data.commodities;
                this.isCardBusy = false
            })
        },
        fetchCommodityResources() {
            this.isFormBusy = true
            axios.get('/api/wallet/get-resource-commodity', {
                params: {
                    query: this.query
                }
            }).then((response) => {
                this.commodityResources = response.data.commodityResources;
            }).then(() => {
                this.isFormBusy = false
            })
        },
        listenForChanges() {
            Echo.private('my-wallet.' + this.user_id)
                .listen('WatchlistCommodityEvent', (e) => {
                    console.log(e)
                    let commodity = this.commodities.find((commodity) => commodity.id === e.id);
                    if (commodity) {
                        let index = this.commodities.indexOf(commodity);
                        this.commodities[index].prev_day_close_price = e.prev_day_close_price;
                        this.commodities[index].current_price = e.current_price;
                        this.commodities[index].percent_change = e.percent_change;
                    } else {
                        this.commodities.push(e)
                    }
                })
        },
        resetResourceForm() {
            this.query = ''
            this.commodityResources = []
        },
        resetUpdateForm() {
            this.updateSoon = {
                index: null,
                id: null,
                amount: null
            }
        },
        store() {
            axios.post('/api/wallet/store-commodity', {
                id: this.updateSoon.id,
                amount: 0 + this.updateSoon.amount
            }).then((response) => {
                if (response.data.status) {
                    this.$bvToast.toast('Commodity has been added to wallet.', {
                        title: `Commodity Wallet`,
                        variant: 'success',
                        solid: true
                    })
                    if (this.updateSoon.index != null) {
                        this.$delete(this.commodityResources, this.updateSoon.index)
                    }
                    this.fetchCommoditys()
                } else {
                    this.$bvToast.toast('Something bad occur while adding to wallet.', {
                        title: `Commodity Wallet`,
                        variant: 'danger',
                        solid: true
                    })
                    console.error(response)
                }
            }).catch((error) => {
                this.$bvToast.toast('Something bad occur while adding to wallet.', {
                    title: `Commodity Wallet`,
                    variant: 'danger',
                    solid: true
                })
                console.error(error)
            })
        },
        remove({commodity, index}) {
            axios.post('/api/wallet/remove-commodity', {
                id: commodity.id
            }).then((response) => {
                if (response.data.status) {
                    this.$bvToast.toast('Commodity has been removed from wallet.', {
                        title: `Commodity Wallet`,
                        variant: 'success',
                        solid: true
                    })
                    this.$delete(this.commodities, index)
                } else {
                    this.$bvToast.toast('Something bad occur while removing.', {
                        title: `Commodity Wallet`,
                        variant: 'danger',
                        solid: true
                    })
                    console.error(response)
                }
            }).catch((error) => {
                this.$bvToast.toast('Something bad occur while removing.', {
                    title: `Commodity Wallet`,
                    variant: 'danger',
                    solid: true
                })
                console.error(error)
            })
        },
        confirmRemoveDialog(commodity, index) {
            this.deleteSoon = {commodity, index}
            this.$refs["delete-confirm"].show()
        },
        confirmRemoveCallback(e) {
            if (e.trigger === 'ok') {
                this.remove(this.deleteSoon)
            }
        },
        triggerAddForm(commodity, index) {
            this.updateSoon.index = index
            this.updateSoon.id = commodity.id
            this.$refs["amount-form"].show()
        },
        triggerUpdateForm(commodity) {
            this.updateSoon.id = commodity.id
            this.updateSoon.amount = commodity.amount
            this.$refs["amount-form"].show()
        }
    }
}
</script>

<style scoped>

</style>
