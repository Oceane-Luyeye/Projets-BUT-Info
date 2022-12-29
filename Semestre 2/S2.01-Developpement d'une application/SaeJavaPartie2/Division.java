public class Division extends Operation {
    
    
    public Division(Expression operande1, Expression operande2){
        super(operande1,operande2);
    }
    
    //retourne un int representant le resultat de loperation
    public int valeur() {
        return this.getOperande1().valeur() / this.getOperande2().valeur();
    }
    
    // retourne un String representant loperation
    public String toString() {
        return "(" + this.getOperande1() + "/" + this.getOperande2() + ")";
    }   


    
/*public division(Nombre op1, Nombre op2) throws OperandeException{

    if(op1 or op2 == 0)
        throws new OperandeException();
    pas sur lol
}*/


}
