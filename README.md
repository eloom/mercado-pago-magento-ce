# Mercado Pago Intermediador de Pagamentos para Magento

Integração com a API Transparente de Pagamento do Mercado Pago.

## Recursos

- [x] processa pagamentos nos ambientes **Sandbox** e **Produção** da Yapay

- [x] apresenta os **juros** em bloco separado no review do pedido e da fatura, nas áreas da administração e do cliente
 
- [x] apresenta os **descontos** em bloco separado no review do pedido e da fatura, nas áreas da administração e do cliente 

- [x] permite escolher os status de pedido para **novos pedidos** e para **pedidos aprovados**

- [x] sincronização automática dos status de pagamento do pedido. A sincronização ocorre no sentido Mercado Pago -> Loja Magento

- [x] sincronização manual do pagamento nos detalhes do pedido. A sincronização ocorre no sentido Mercado Pago -> Loja Magento

- [x] suporte a campanhas criadas no Mercado Pago

#### Pagamentos com **Cartões de Crédito**

- [x] desconto à vista

- [x] valor da parcela mínima

 
#### Pagamentos com **Boleto Bancário**

- [x] instruções ao cliente que são inseridas no checkout

- [x] permite configurar o cancelamento dos pedidos automaticamente por compras realizadas **na sexta-feira**, **no sábado** e **entre domingo e quinta-feira**


## Perguntas Frequentes


## Dependências


O módulo de pagamento Yapay Intermediador para Magento precisa do módulo [Bootstrap para Magento CE](https://github.com/eloom/bootstrap-magento-ce)

O módulo de pagamento Yapay Intermediador para Magento funciona apenas no [Checkout para Magento CE](https://github.com/eloom/checkout-magento-ce)


## Compatibilidade

- [x] Magento 1.9.3.x

- [x] PHP/PHP-FPM 5.6

## Começando

Os projetos da élOOm utilizam o [Apache Ant](https://ant.apache.org/) para publicar o projeto nos ambientes de **desenvolvimento** e de **teste** e para gerar os pacotes para o **ambiente de produção**.

- Publicando no **ambiente local**

	- no arquivo **build-desenv.properties**, informe o path do **Document Root** na propriedade "projetos.path";
	
	- na raiz deste projeto, execute, no prompt, o comando ```ant -f build-desenv.xml```.
	
	
	> a tarefa Ant irá copiar todos os arquivos do projeto no seu Magento e limpar a cache.
	

- Publicando para o **ambiente de produção**

	- na raiz deste projeto, execute, no prompt, o comando ```ant -f build-producao.xml```.
	
	
	> a tarefa Ant irá gerar um pacote no formato .zip, no caminho definido na propriedade "projetos.path", do arquivo **build-producao.properties**.

	> os arquivos .css e .js serão comprimidos automáticamente usando o [YUI Compressor](https://yui.github.io/yuicompressor/).
	

## Release Notes

### [1.0.0] - 2016-01-03

#### Versão inicial

### [1.0.7] - 2018-05-03

#### Melhorias e correções de bugs