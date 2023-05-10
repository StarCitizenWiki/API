<template>
  <table class="table table-striped m-0 table-responsive w-100" v-if="shops.length">
    <tr v-for="shop in shops" :key="shops.uuid">
      <td>{{ shop.name_raw }}</td>
      <td v-html="getPrice(shop)"></td>
    </tr>
  </table>
  <span v-else>-</span>
</template>

<script>
export default {
  name: "ItemPriceTable",
  props: {
    shops: Array,
  },
  methods: {
    getPrice: function (shop) {
        if (typeof shop.items !== 'undefined') {
            if (shop.items[0].rentable === true) {
                return `Miete:<br>
1 Tag ${shop.items[0].rental_price_days.duration_1} aUEC<br>
3 Tag ${shop.items[0].rental_price_days.duration_3} aUEC<br>
7 Tag ${shop.items[0].rental_price_days.duration_7} aUEC<br>
30 Tage ${shop.items[0].rental_price_days.duration_30} aUEC`
            }

            let prefix = ''
            if (shop.items[0].buyable === true) {
                prefix = 'Kauf: '
            }

            if (shop.items[0].sellable === true) {
                prefix = 'Verkauf: '
            }

            if (shop.items[0].sellable === true && shop.items[0].buyable === true) {
                prefix = 'Kauf / Verkauf: '
            }

            return `${prefix}${Math.floor(shop.items[0].price_calculated)} aUEC`;
      }

      return '-';
    }
  }
}
</script>

<style scoped>

</style>