module.exports = {
  root: true,
  env: {
    node: true,
    browser: true,
    es2021: true,
  },
  extends: [
    'plugin:vue/vue3-recommended',
    'eslint:recommended',
    'prettier'
  ],
  parserOptions: {
    ecmaVersion: 2021,
    sourceType: 'module'
  },
  rules: {
    'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'vue/multi-word-component-names': 'off',
    'vue/require-default-prop': 'off',
    'vue/no-v-html': 'off',
    'vue/max-attributes-per-line': ['error', {
      singleline: {
        max: 3
      },      
      multiline: {
        max: 1
      }
    }],
    'vue/html-self-closing': ['error', {
      html: {
        void: 'always',
        normal: 'never',
        component: 'always'
      },
      svg: 'always',
      math: 'always'
    }],
    'vue/component-name-in-template-casing': ['error', 'PascalCase', {
      registeredComponentsOnly: true,
      ignores: []
    }],
    'vue/no-unused-vars': 'error',
    'vue/script-setup-uses-vars': 'error',
    'vue/no-mutating-props': 'error',
    'vue/no-unused-components': 'error',
    'vue/no-template-shadow': 'error',
    'vue/no-use-v-if-with-v-for': 'error',
    'vue/valid-v-for': 'error',
    'vue/valid-v-if': 'error',
    'vue/valid-v-else': 'error',
    'vue/valid-v-else-if': 'error',
    'vue/valid-v-on': 'error',
    'vue/valid-v-bind': 'error',
    'vue/valid-v-model': 'error',
    'vue/valid-v-slot': 'error',
    'vue/valid-template-root': 'error',
    'vue/return-in-computed-property': 'error',
    'vue/require-prop-types': 'error',
    'vue/require-explicit-emits': 'error',
    'vue/order-in-components': 'error',
    'vue/no-side-effects-in-computed-properties': 'error',
    'vue/no-reserved-component-names': 'error',
    'vue/no-ref-as-operand': 'error',
    'vue/no-multiple-template-root': 'error',
    'vue/no-duplicate-attributes': 'error',
    'vue/no-computed-properties-in-data': 'error',
    'vue/no-async-in-computed-properties': 'error',
    'vue/attributes-order': 'error'
  }
} 