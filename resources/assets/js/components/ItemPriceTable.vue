<template>
  <table class="table table-striped m-0 table-responsive w-100" v-if="shops.length">
    <tr v-for="shop in shops" :key="shops.uuid">
      <td>{{ shop.name_raw }}</td>
      <td>{{ getPrice(shop) }}</td>
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
        return `${Math.floor(shop.items.data[0].price_calculated)} aUEC`;
      }

      return '-';
    }
  }
}
</script>

<style scoped>

</style>