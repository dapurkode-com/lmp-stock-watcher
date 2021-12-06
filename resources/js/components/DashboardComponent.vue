<template>
    <div class="container-fluid px-5">
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ sumCommodity| abbr }}</h3>

                        <p>Commodity</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <a class="small-box-footer" href="/my-wallet/commodity">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ sumCrypto | abbr }}</h3>

                        <p>Cryptocurrency</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <a class="small-box-footer" href="/my-wallet/crypto">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ sumUsStock | abbr }}</h3>

                        <p>US Stock</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <a class="small-box-footer" href="/my-wallet/us-stock">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ sumIdxStock | abbr }}</h3>

                        <p>Indonesia Stock</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-landmark"></i>
                    </div>
                    <a class="small-box-footer" href="/my-wallet/idx-stock">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <div class="row">
            <div class="col-sm-12">
                <b-card title="Summary">
                    <b-card-body class="col-sm-12 col-xl-4 offset-xl-4">
                        <doughnut-chart :chart-data="summaryDataSet"></doughnut-chart>
                    </b-card-body>
                </b-card>
            </div>
        </div>
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
            cryptos: [],
            idxStocks: [],
            usStocks: [],
        }
    },
    created() {
        this.fetchCommodities()
        this.fetchCryptos()
        this.fetchIdxStocks()
        this.fetchUsStocks()
        this.listenForChanges()
    },
    filters: {
        abbr: function(num) {
            if(num > 999 && num < 1000000){
                return (num/1000).toFixed(1) + 'K';
            }else if(num > 1000000){
                return (num/1000000).toFixed(1) + 'M';
            }else if(num < 900){
                return num; // if value < 1000, nothing to do
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
        fetchCommodities() {
            axios.get('/api/wallet/commodities').then((response) => {
                this.commodities = response.data.commodities;
            })
        },
        fetchCryptos() {
            axios.get('/api/wallet/cryptos').then((response) => {
                this.cryptos = response.data.cryptos;
            })
        },
        fetchIdxStocks() {
            axios.get('/api/wallet/idx-stocks').then((response) => {
                this.idxStocks = response.data.stocks;
            })
        },
        fetchUsStocks() {
            axios.get('/api/wallet/us-stocks').then((response) => {
                this.usStocks = response.data.data;
            })
        },
        listenForChanges() {
            Echo.channel('my-wallet')
                .listen('WatchlistUsStockEvent', (e) => {
                    console.log("WatchlistUsStockEvent: ")
                    console.log(e)
                    let stock = this.usStocks.find((stock) => stock.id === e.id);
                    if (stock) {
                        let index = this.usStocks.indexOf(stock);
                        this.usStocks[index].prev_day_close_price = e.prev_day_close_price;
                        this.usStocks[index].current_price = e.current_price;
                        this.usStocks[index].change = e.change;
                        this.usStocks[index].percent_change = e.percent_change;
                    } else {
                        this.usStocks.push(e)
                    }
                })
                .listen('WatchlistIdxStockEvent', (e) => {
                    console.log("WatchlistIdxStockEvent: ")
                    console.log(e)
                    let stock = this.idxStocks.find((stock) => stock.id === e.id);
                    if (stock) {
                        let index = this.idxStocks.indexOf(stock);
                        this.idxStocks[index].prev_day_close_price = e.prev_day_close_price;
                        this.idxStocks[index].current_price = e.current_price;
                        this.idxStocks[index].change = e.change;
                        this.idxStocks[index].percent_change = e.percent_change;
                    } else {
                        this.idxStocks.push(e)
                    }
                })
                .listen('WatchlistCryptoEvent', (e) => {
                    console.log("WatchlistCryptoEvent: ")
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
                .listen('WatchlistCommodityEvent', (e) => {
                    console.log("WatchlistCommodityEvent: ")
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
    },
    computed: {
        sumCommodity: function(){
            return this.commodities.map(a => a.amount * a.unit * a.current_price).reduce((prev, next) => prev + next, 0)
        },
        sumCrypto: function(){
            return this.cryptos.map(a => a.amount * a.unit * a.current_price).reduce((prev, next) => prev + next, 0)
        },
        sumUsStock: function(){
            return this.usStocks.map(a => a.amount * a.unit * a.current_price).reduce((prev, next) => prev + next, 0)
        },
        sumIdxStock: function(){
            return this.idxStocks.map(a => a.amount * a.unit * a.current_price).reduce((prev, next) => prev + next, 0)
        },
        summaryDataSet: function () {
            let labels = ['Commodity', 'Cryptocurrency', 'Indonesian Stock', 'US Stock']
            let data = [this.sumCommodity, this.sumCrypto, this.sumIdxStock, this.sumUsStock]
            let backgrounds = ['#0275d8', '#5cb85c', '#d9534f', '#f0ad4e' ]
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
    }
}
</script>

<style scoped>

</style>
