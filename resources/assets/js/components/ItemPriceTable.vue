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
      if (typeof shop.items.data !== 'undefined') {
        if (shop.items.data[0].rentable === true) {
          return `Miete:<br>
1 Tag ${shop.items.data[0].rental_price_days[1]} aUEC<br>
3 Tag ${shop.items.data[0].rental_price_days[3]} aUEC<br>
7 Tag ${shop.items.data[0].rental_price_days[7]} aUEC<br>
30 Tage ${shop.items.data[0].rental_price_days[30]} aUEC`
        }

        let prefix = ''
        if (shop.items.data[0].buyable === true) {
          prefix = 'Kauf: '
        }

        if (shop.items.data[0].sellable === true) {
          prefix = 'Verkauf: '
        }

        if (shop.items.data[0].sellable === true && shop.items.data[0].buyable === true) {
          prefix = 'Kauf / Verkauf: '
        }

        return `${prefix}${Math.floor(shop.items.data[0].price_calculated)} aUEC`;
      }

      return '-';
    }
  }
}
</script>

<style scoped>

</style>