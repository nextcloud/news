<template>
  <p>This is a test component {{msg}}</p>
</template>

<script>
module.exports = {
  data () {
    return {
      msg: "asdfasdfjkl"
    }
  }
}
app.value('AnotherComponent', module.exports);
</script>
