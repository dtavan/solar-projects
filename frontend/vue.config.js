module.exports = {
  lintOnSave: 'warning',
  devServer: {
    watchOptions: {
      ignored: /node_modules/,
      aggregateTimeout: 300,
      // This is needed to make watching work inside Docker because of the way
      // Docker handles volume mounts.
      poll: 500,
    },
  },
};
