<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-eye"></i> Watchlist</h3>
                            <small class="font-italic"> per shares</small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th class="text-right">Previous</th>
                                    <th class="text-right">Current</th>
                                    <th class="text-right">Change</th>
                                    <th class="text-right">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="stocks.length == 0">
                                    <td colspan="7" class="text-center">No Data</td>
                                </tr>
                                <tr v-for="(stock, index) in sortedStocks" :key="stock.id">
                                    <td>{{ ++index }}</td>
                                    <th>{{ stock.symbol }}</th>
                                    <td>{{ stock.name }}</td>
                                    <td class="text-right">{{ stock.prev_price != null ? stock.prev_price.toLocaleString() : '-' }}</td>
                                    <td class="text-right">{{ stock.current_price != null ? stock.current_price.toLocaleString() : '-' }}</td>
                                    <td class="text-right">{{ stock.change != null ? stock.change.toLocaleString() : '-' }}</td>
                                    <td class="text-right"><span class="badge" :class="{'badge-success': stock.change > 0, 'badge-secondary': stock.change == 0, 'badge-danger': stock.change < 0}">{{ stock.percent_change != null ? stock.percent_change.toLocaleString() : '-' }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Currency in United State Dollar (USD)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>

</style>

<script>
    export default {
        data() {
            return {
                stocks: []
            }
        },
        created() {
            this.fetchStocks();
            this.listenForChanges();
        },
        methods: {
            fetchStocks() {
                axios.get('/api/us-stock-list').then((response) => {
                    this.stocks = response.data;
                })
            },
            listenForChanges() {
                Echo.channel('us-stock')
                .listen('.us-stock-watcher', (e) => {
                    console.log(e);
                    var stock = this.stocks.find((stock) => stock.id === e.id);
                        if(stock){
                            var index = this.stocks.indexOf(stock);
                            this.stocks[index].prev_price = e.prev_price;
                            this.stocks[index].current_price = e.current_price;
                            this.stocks[index].change = e.change;
                            this.stocks[index].percent_change = e.percent_change;
                        }
                        else {
                            this.stocks.push(e)
                        }
                    })
            }
        },
        computed: {
            sortedStocks() {
                function compare(a, b) {
                    if (a.symbol < b.symbol)
                        return -1;
                    if (a.symbol > b.symbol)
                        return 1;
                    return 0;
                }
                return this.stocks.sort(compare)
            }
        }
    }

</script>
