<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-xs-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Watchlist (IDR)</h3></div>

                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th class="text-right">Last</th>
                                    <th class="text-right">Buy</th>
                                    <th class="text-right">Sell</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="cryptos.length == 0">
                                    <td colspan="6" class="text-center">No Data</td>
                                </tr>
                                <tr v-for="(crypto, index) in sortedCryptos" :key="crypto.id">
                                    <td>{{ ++index }}</td>
                                    <th>{{ crypto.symbol }}</th>
                                    <td>{{ crypto.name }}</td>
                                    <td class="text-right">{{ crypto.last != null ? crypto.last.toLocaleString() : '-' }}</td>
                                    <td class="text-right">{{ crypto.buy != null ? crypto.buy.toLocaleString() : '-' }}</td>
                                    <td class="text-right">{{ crypto.sell != null  ? crypto.sell.toLocaleString() : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
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
                cryptos: []
            }
        },
        created() {
            this.fetchCryptos();
            this.listenForChanges();
        },
        methods: {
            fetchCryptos() {
                axios.get('/api/crypto-list').then((response) => {
                    this.cryptos = response.data;
                })
            },
            listenForChanges() {
                Pusher.logToConsole = true;
                Echo.channel('crypto')
                .listen('.CryptoEvent', (e) => {
                    console.log(e);
                    var crypto = this.cryptos.find((crypto) => crypto.id === e.id);
                        if(crypto){
                            var index = this.cryptos.indexOf(crypto);
                            this.cryptos[index].last = e.last;
                            this.cryptos[index].buy = e.buy;
                            this.cryptos[index].sell = e.sell;
                        }
                        else {
                            this.cryptos.push(e)
                        }
                    })
            }
        },
        computed: {
            sortedCryptos() {
                function compare(a, b) {
                    if (a.symbol < b.symbol)
                        return -1;
                    if (a.symbol > b.symbol)
                        return 1;
                    return 0;
                }
                return this.cryptos.sort(compare)
            }
        }
    }

</script>
