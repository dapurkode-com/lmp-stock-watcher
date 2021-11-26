<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-eye"></i> Watchlist</h3>
                            <b-button v-b-modal.modal-1 size="sm" variant="outline-light"><i class="fas fa-plus"></i>
                                Add Watchlist
                            </b-button>
                        </div>
                    </div>

                    <div class="card-body p-0 overflow-auto">
                        <b-overlay :show="isCardBusy" rounded="sm" no-wrap></b-overlay>
                        <b-table :fields="stockFields" :items="stocks" show-empty>
                            <template #cell(prev_day_close_price)="data">
                                {{
                                    data.item.prev_day_close_price != null ? Number(data.item.prev_day_close_price).toLocaleString() : '-'
                                }}
                            </template>
                            <template #cell(current_price)="data">
                                {{ data.item.current_price ? Number(data.item.current_price).toLocaleString() : '-' }}
                            </template>
                            <template #cell(change)="data">
                                <span
                                    :class="{'badge-success' : data.item.change > 0, 'badge-danger' : data.item.change < 0}"
                                    class="badge">{{
                                        data.item.change != null ? Number(data.item.change).toLocaleString() : '-'
                                    }}</span>
                            </template>
                            <template #cell(percent_change)="data">
                                <span
                                    :class="{'badge-success' : data.item.percent_change > 0, 'badge-danger' : data.item.percent_change < 0}"
                                    class="badge">{{
                                        data.item.percent_change != null ? Number(data.item.percent_change).toLocaleString() : '-'
                                    }}</span>
                            </template>
                            <template #cell(actions)="row">
                                <b-button class="mr-1 float-right" size="sm" title="Remove"
                                          variant="danger" @click="confirmRemoveDialog(row.item, row.index)">
                                    <i class="fas fa-trash"></i>
                                </b-button>
                            </template>
                        </b-table>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Currency in Indonesian Rupiah (IDR) -- convert with exchange rate from Bank Indonesia buying price.</small>
                    </div>
                </div>
            </div>
        </div>
        <b-modal id="modal-1" hide-footer scrollable size="lg"
                 title="Add US stock watchlist" @hide="resetResourceForm" @show="resetResourceForm">
            <b-overlay :show="isFormBusy" rounded="sm" no-wrap></b-overlay>
            <b-form @submit.stop.prevent="fetchStockResources">
                <b-input-group class="mb-3">
                    <b-form-input v-model="query" autofocus placeholder="Type stock name or symbols"
                                  type="text"></b-form-input>
                    <b-input-group-append>
                        <b-button class="pull-right" type="submit" variant="primary">Search</b-button>
                    </b-input-group-append>
                </b-input-group>
                <b-table :busy="isFormBusy" :fields="stockResourceFields" :items="stockResources" bordered hover small striped>
                    <template #cell(actions)="row">
                        <b-button class="mr-1 float-right" size="sm" title="Add"
                                  variant="success" @click="store(row.item, row.index)">
                            <i class="fas fa-plus"></i>
                        </b-button>
                    </template>
                </b-table>
            </b-form>
        </b-modal>
        <b-modal ref="delete-confirm" cancel-title="No" ok-title="Yes" ok-variant="danger" title="Remove Confirmation"
                 @hide="confirmRemoveCallback">Are you sure to remove this ?
        </b-modal>
    </div>
</template>

<style>

</style>

<script>
export default {
    props: [
        'user_id'
    ],
    data() {
        return {
            stocks: [],
            stockFields: [
                {'key': 'id', 'label': '#'},
                'symbol',
                'name',
                {'key': 'prev_day_close_price', 'label': 'Prev', 'tdClass': 'text-right'},
                {'key': 'current_price', 'label': 'Current', 'tdClass': 'text-right'},
                {'key': 'change', 'tdClass': 'text-right'},
                {'key': 'percent_change', 'label': '1d %', 'tdClass': 'text-right'},
                {'key': 'actions', 'label': ''}
            ],
            query: '',
            stockResources: [],
            stockResourceFields: [
                'symbol',
                {'key': 'description', 'label': 'Name'},
                {'key': 'actions', 'label': ''}
            ],
            deleteSoon: {},
            isFormBusy: false,
            isCardBusy: false
        }
    },
    created() {
        this.fetchStocks();
        this.listenForChanges();
    },
    methods: {
        fetchStocks() {
            this.isCardBusy = true
            axios.get('/api/watchlist/us-stocks').then((response) => {
                this.stocks = response.data.data;
                this.isCardBusy = false
            })
        },
        listenForChanges() {
            Echo.private('watchlist.' + this.user_id)
                .listen('WatchlistUsStockEvent', (e) => {
                    console.log(e)
                    let stock = this.stocks.find((stock) => stock.id === e.id);
                    if (stock) {
                        let index = this.stocks.indexOf(stock);
                        this.stocks[index].prev_day_close_price = e.prev_day_close_price;
                        this.stocks[index].current_price = e.current_price;
                        this.stocks[index].change = e.change;
                        this.stocks[index].percent_change = e.percent_change;
                    } else {
                        this.stocks.push(e)
                    }
                })
        },
        resetResourceForm() {
            this.query = ''
            this.stockResources = []
        },
        fetchStockResources() {
            this.isFormBusy = true
            axios.get('/api/watchlist/get-resource-us-stock', {
                params: {
                    query: this.query
                }
            }).then((response) => {
                this.stockResources = response.data.stockResources;
            }).then(()=>{
                this.isFormBusy = false
            })
        },
        store(stock, index) {
            axios.post('/api/watchlist/store-us-stock', {
                symbol: stock.symbol,
                name: stock.description,
            }).then((response) => {
                if (response.data.status) {
                    this.$bvToast.toast('Stock has been added to watchlist.', {
                        title: `US Stock Watchlist`,
                        variant: 'success',
                        solid: true
                    })
                    this.$delete(this.stockResources, index)
                    this.fetchStocks()
                } else {
                    this.$bvToast.toast('Something bad occur while adding to watchlist.', {
                        title: `US Stock Watchlist`,
                        variant: 'danger',
                        solid: true
                    })
                    console.error(response)
                }
            }).catch((error) => {
                this.$bvToast.toast('Something bad occur while adding to watchlist.', {
                    title: `US Stock Watchlist`,
                    variant: 'danger',
                    solid: true
                })
                console.error(error)
            })
        },
        remove({stock, index}) {
            axios.post('/api/watchlist/remove-us-stock', {
                id: stock.id
            }).then((response) => {
                if (response.data.status) {
                    this.$bvToast.toast('Stock has been removed from watchlist.', {
                        title: `US Stock Watchlist`,
                        variant: 'success',
                        solid: true
                    })
                    this.$delete(this.stocks, index)
                } else {
                    this.$bvToast.toast('Something bad occur while removing.', {
                        title: `US Stock Watchlist`,
                        variant: 'danger',
                        solid: true
                    })
                    console.error(response)
                }
            }).catch((error) => {
                this.$bvToast.toast('Something bad occur while removing.', {
                    title: `US Stock Watchlist`,
                    variant: 'danger',
                    solid: true
                })
                console.error(error)
            })
        },
        confirmRemoveDialog(stock, index) {
            this.deleteSoon = {stock, index}
            this.$refs["delete-confirm"].show()
        },
        confirmRemoveCallback(e) {
            if (e.trigger === 'ok') {
                this.remove(this.deleteSoon)
            }
        }
    }
}

</script>
