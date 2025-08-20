-- Inserindo dados iniciais
INSERT INTO usuarios (nome, email, senha) VALUES ('João Silva', 'email1@gmail.com', 'senha123');
INSERT INTO usuarios (nome, email, senha) VALUES ('Maria Souza', 'maria@email.com', 'senha456');

INSERT INTO produtos (nome, descricao, preco, estoque) VALUES ('Notebook', 'Laptop de última geração', 3500.00, 10);
INSERT INTO produtos (nome, descricao, preco, estoque) VALUES ('Mouse', 'Mouse óptico sem fio', 150.00, 50);

INSERT INTO pedidos (id_usuario, total) VALUES (1, 3650.00);
INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (1, 1, 1, 3500.00);
INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (1, 2, 1, 150.00);
